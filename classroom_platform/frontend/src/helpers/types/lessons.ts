export interface T_FileItem {
  title: string;
  file: string;
}

export interface T_LessonItem {
  title: string;
  files: T_FileItem[];
}

export interface T_TagGroup {
  tag: string;
  lessons: T_LessonItem[];
}

export interface T_LessonsGroup {
  programName: string;
  tags: T_TagGroup[];
}
