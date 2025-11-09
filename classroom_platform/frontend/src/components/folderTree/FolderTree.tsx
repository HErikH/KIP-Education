import { useEffect } from "react";
import { FolderItem } from "../folderItem/FolderItem";
import { useFetchLessons } from "@/store/rooms/actions";
import { useLessonsData } from "@/store/rooms/selectors";
import "./style.scss";
import { fetchUrlAsFile } from "@/helpers/functions/utils";

type T_Props = {
  uploadFile: (file: File) => Promise<void>;
};

// FIXME: Also maybe access will be closed for students only role teacher as in the ParticipantsList
export function FolderTree({ uploadFile }: T_Props) {
  const data = useLessonsData();
  const fetchLessons = useFetchLessons();

  async function uploadUrlsAsFile(fileUrl: string, fileTitle: string) {
    const file = await fetchUrlAsFile(fileUrl, fileTitle);

    uploadFile(file);
  }

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
                    // TODO: Just load directly inside canvas (save in the redux)
                    // and then pass through socket io
                    <button
                      key={file.file}
                      onClick={() => uploadUrlsAsFile(file.file, file.title)}
                    >
                      {file.title}
                    </button>
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
