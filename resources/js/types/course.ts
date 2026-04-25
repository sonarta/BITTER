export type Instructor = {
    name: string;
    title: string;
};

export type Course = {
    slug: string;
    title: string;
    tagline: string;
    description: string;
    category: string;
    level: 'Beginner' | 'Intermediate' | 'Advanced';
    duration_hours: number;
    lessons_count: number;
    students: number;
    rating: number;
    price: number;
    cover: string;
    instructor: Instructor;
};

export type CourseLesson = {
    title: string;
    duration: string;
    preview: boolean;
};

export type CourseModule = {
    title: string;
    lessons: CourseLesson[];
};

export type LessonRef = {
    slug: string;
    title: string;
};

export type LessonResource = {
    title: string;
    url: string;
    type: string;
};

export type PlayerLesson = CourseLesson & {
    slug: string;
    completed: boolean;
    module_title: string;
    description: string;
    video_url: string;
    resources: LessonResource[];
    transcript: string;
};

export type PlayerModule = {
    title: string;
    lessons: (CourseLesson & { slug: string; completed: boolean })[];
};

export type PlayerCourse = {
    slug: string;
    title: string;
    instructor: Instructor;
};

export type EnrolledCourse = Course & {
    progress: number;
    last_lesson: string;
};
