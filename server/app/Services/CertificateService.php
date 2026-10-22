<?php

namespace App\Services;

use App\Mail\CourseCertificateMail;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CertificateService
{
    /**
     * Get or issue a certificate for a user and course, and send email if not yet sent.
     */
    public function issueCertificate(User $user, Course $course, Enrollment $enrollment): Certificate
    {
        $certificate = Certificate::firstOrCreate(
            [
                'user_id'   => $user->id,
                'course_id' => $course->id,
            ],
            [
                'uuid'               => (string) Str::uuid(),
                'certificate_number' => Certificate::generateCertificateNumber(),
                'enrollment_id'      => $enrollment->id,
                'recipient_name'     => $user->name,
                'course_title'       => $course->title,
                'issued_at'          => now(),
                'email_sent_at'      => null,
            ]
        );

        // If email was not yet sent, send it now
        if (!$certificate->email_sent_at) {
            $this->sendCertificateEmail($certificate, $user->email);
        }

        return $certificate;
    }

    /**
     * Generate binary PDF string for a certificate.
     */
    public function generatePdf(Certificate $certificate): string
    {
        $verifyUrl = $this->getVerificationUrl($certificate);

        $pdf = Pdf::loadView('certificates.template', [
            'recipient_name'     => $certificate->recipient_name,
            'course_title'       => $certificate->course_title,
            'certificate_number' => $certificate->certificate_number,
            'issued_at'          => $certificate->issued_at,
            'verify_url'         => $verifyUrl,
        ]);

        $pdf->setPaper('a4', 'landscape');
        $pdf->setOption([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'Georgia',
        ]);

        return $pdf->output();
    }

    /**
     * Send certificate email with PDF attachment.
     */
    public function sendCertificateEmail(Certificate $certificate, ?string $toEmail = null): bool
    {
        $email = $toEmail ?? $certificate->user?->email;
        if (!$email) {
            Log::warning("Cannot send certificate email: no email found for certificate {$certificate->id}");
            return false;
        }

        try {
            $pdfContent = $this->generatePdf($certificate);
            $verifyUrl  = $this->getVerificationUrl($certificate);

            Mail::to($email)->send(new CourseCertificateMail($certificate, $pdfContent, $verifyUrl));

            $certificate->update(['email_sent_at' => now()]);
            Log::info("Certificate {$certificate->certificate_number} successfully emailed to {$email}");
            return true;
        } catch (\Throwable $e) {
            Log::error("Failed to email certificate {$certificate->certificate_number} to {$email}: " . $e->getMessage(), [
                'exception' => $e,
            ]);
            return false;
        }
    }

    /**
     * Construct public verification URL for the frontend.
     */
    public function getVerificationUrl(Certificate $certificate): string
    {
        $frontendUrl = rtrim(config('app.frontend_url') ?: env('FRONTEND_URL', 'http://localhost:5174'), '/');
        return "{$frontendUrl}/certificates/verify/{$certificate->certificate_number}";
    }
}
