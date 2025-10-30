import { LessonsModel } from "../models/index.js";
import { filterFiles, normalizeFiles } from "../helpers/functions/lessons.js";

// Filter out audio/video

export class LessonsService {
  static async getLessonsStructure() {
    const lessons = await LessonsModel.getAllLessons();

    const programs = {};

    lessons.forEach((lesson) => {
      // Skip Book tag
      if (lesson.tag?.toLowerCase() === "book") return;

      const programName = lesson.program_name;
      const tag = lesson.tag || "default";

      // Ensure program entry exists
      if (!programs[programName]) programs[programName] = {};

      // Ensure tag entry exists
      if (!programs[programName][tag]) programs[programName][tag] = [];

      // Normalize and filter files
      let files = normalizeFiles(lesson.files);
      files = filterFiles(files);

      programs[programName][tag].push({
        title: lesson.title,
        files,
      });
    });

    // Convert to array format for API output
    return Object.entries(programs).map(([programName, tagsObj]) => ({
      programName,
      tags: Object.entries(tagsObj).map(([tag, lessons]) => ({
        tag,
        lessons,
      })),
    }));
  }
}
