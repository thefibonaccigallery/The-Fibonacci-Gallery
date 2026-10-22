<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CertificateController extends Controller
{
    public function __construct(
        protected CertificateService $certificateService
    ) {}

    /**
     * List all certificates belonging to the authenticated student.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $certificates = Certificate::where('user_id', $user->id)
            ->with(['course:id,uuid,title,thumbnail,level'])
            ->orderBy('issued_at', 'desc')
            ->get();

        $data = $certificates->map(function ($cert) {
            return [
                'uuid'               => $cert->uuid,
                'certificate_number' => $cert->certificate_number,
                'recipient_name'     => $cert->recipient_name,
                'course_title'       => $cert->course_title,
                'issued_at'          => $cert->issued_at,
                'email_sent_at'      => $cert->email_sent_at,
                'verify_url'         => $this->certificateService->getVerificationUrl($cert),
                'course'             => $cert->course ? [
                    'uuid'          => $cert->course->uuid,
                    'title'         => $cert->course->title,
                    'level'         => $cert->course->level,
                    'thumbnail_url' => $cert->course->thumbnail_url,
                ] : null,
            ];
        });

        return response()->json([
            'success'      => true,
            'certificates' => $data,
        ]);
    }

    /**
     * Get details of a single certificate.
     */
    public function show($uuid)
    {
        $user = Auth::user();

        $certificate = Certificate::where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->with(['course:id,uuid,title,thumbnail,level'])
            ->firstOrFail();

        return response()->json([
            'success'     => true,
            'certificate' => [
                'uuid'               => $certificate->uuid,
                'certificate_number' => $certificate->certificate_number,
                'recipient_name'     => $certificate->recipient_name,
                'course_title'       => $certificate->course_title,
                'issued_at'          => $certificate->issued_at,
                'email_sent_at'      => $certificate->email_sent_at,
                'verify_url'         => $this->certificateService->getVerificationUrl($certificate),
                'course'             => $certificate->course ? [
                    'uuid'          => $certificate->course->uuid,
                    'title'         => $certificate->course->title,
                    'level'         => $certificate->course->level,
                    'thumbnail_url' => $certificate->course->thumbnail_url,
                ] : null,
            ],
        ]);
    }

    /**
     * Download certificate as PDF.
     */
    public function download($uuid)
    {
        $user = Auth::user();

        $certificate = Certificate::where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $pdfContent = $this->certificateService->generatePdf($certificate);
        $filename   = "Fibonacci-Certificate-{$certificate->certificate_number}.pdf";

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ]);
    }

    /**
     * Public verification endpoint to verify authenticity of a certificate.
     */
    public function verify($certificateNumber)
    {
        $code = strtoupper(trim($certificateNumber));

        $certificate = Certificate::where('certificate_number', $code)
            ->with(['course:id,uuid,title,level'])
            ->first();

        if (!$certificate) {
            return response()->json([
                'success' => false,
                'valid'   => false,
                'message' => 'The certificate number entered could not be verified in our records.',
            ], 404);
        }

        return response()->json([
            'success'     => true,
            'valid'       => true,
            'certificate' => [
                'uuid'               => $certificate->uuid,
                'certificate_number' => $certificate->certificate_number,
                'recipient_name'     => $certificate->recipient_name,
                'course_title'       => $certificate->course_title,
                'issued_at'          => $certificate->issued_at,
                'academy'            => 'The Fibonacci Gallery',
                'course'             => $certificate->course ? [
                    'uuid'  => $certificate->course->uuid,
                    'title' => $certificate->course->title,
                    'level' => $certificate->course->level,
                ] : null,
            ],
        ]);
    }

    /**
     * Re-send certificate email to the user.
     */
    public function resendEmail($uuid)
    {
        $user = Auth::user();

        $certificate = Certificate::where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $sent = $this->certificateService->sendCertificateEmail($certificate, $user->email);

        if (!$sent) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to send certificate email at this moment. Please try again later.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Certificate has been sent to your email with the PDF attached.',
        ]);
    }
}
