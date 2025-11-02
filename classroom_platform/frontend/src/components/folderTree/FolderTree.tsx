import { useEffect } from "react";
import { FolderItem } from "../folderItem/FolderItem";
import { useFetchLessons } from "@/store/rooms/actions";
import { useLessonsData } from "@/store/rooms/selectors";
import "./style.scss";

// FIXME: Also maybe access will be closed for students only role teacher as in the ParticipantsList
export function FolderTree() {
  const data = useLessonsData();
  const fetchLessons = useFetchLessons();

  useEffect(() => {
    (async () => {
      // FIXME: Think about how will determine which programs lesson
      // will be loaded depending on the teacher bought program
      await fetchLessons();
    })();
  }, []);

  if (!data || data.length === 0) {
    return <p>No lessons found.</p>;
  }

  return (
    <div className="folder-wrapper">
      {data.map((program) => (
        <FolderItem key={program.programName} label={program.programName}>
          {program.tags.map((tag) => (
            <FolderItem key={tag.tag} label={tag.tag}>
              {tag.lessons.map((lesson) => (
                <FolderItem key={lesson.title} label={lesson.title}>
                  {lesson.files.map((file) => (
                    <div key={file.file}>
                      <a
                        href={file.file}
                        target="_blank"
                        rel="noopener noreferrer"
                      >
                        {file.title}
                      </a>
                    </div>
                  ))}
                </FolderItem>
              ))}
            </FolderItem>
          ))}
        </FolderItem>
      ))}
    </div>
  );
}
