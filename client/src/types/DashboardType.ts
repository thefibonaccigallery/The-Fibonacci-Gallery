export interface Student {
  name: string;
}

export interface EnrolledCourse {
  id: number;
  title: string;
  totalLessons: number;
  completedLessons: number;
  thumbnail: string | null;
}

export interface ExploreCourse {
  id: number;
  title: string;
  totalLessons: number;
  description: string;
  thumbnail?: string | null;
}

export type DashboardTab = 'home' | 'courses' | 'explore' | 'profile';
