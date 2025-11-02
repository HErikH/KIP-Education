import type { T_LessonsGroup } from "@/helpers/types/lessons";
import type { T_RoomInfo } from "@/helpers/types/rooms";

export const MOCK_DATA: T_RoomInfo[] = [
  {
    room_id: "2",
    class_id: "classId-20250918-CMK89E",
    user_id: 1,
    room_name: "English A1",
    username: "Erik",
    role: "teacher",
  },
  {
    room_id: "2",
    class_id: "classId-20250918-CMK89E",
    user_id: 2,
    room_name: "English A1",
    username: "Esas",
    role: "student",
  },
  {
    room_id: "2",
    class_id: "classId-20250918-CMK89E",
    user_id: 3,
    room_name: "English A3",
    username: "Vsasf",
    role: "student",
  },
  {
    room_id: "class-4",
    class_id: "classId-20250918-CMK34T",
    user_id: 4,
    room_name: "English A4",
    username: "Fss",
    role: "student",
  },
  {
    room_id: "class-5",
    class_id: "classId-20250918-CMK23C",
    user_id: 5,
    room_name: "English A5",
    username: "Rgesf",
    role: "student",
  },
];

export const MOCK_DATA_LESSONS: T_LessonsGroup[] = [
  {
    programName: "K1",
    tags: [
      {
        tag: "Letters",
        lessons: [
          {
            title: "Lesson 1_Letter Aa_Bb",
            files: [
              {
                title: "Letter (3).pptx",
                file: "uploads/lessons/1/Letter (3).pptx",
              },
              {
                title: "Letter Aa (5).pptx",
                file: "uploads/lessons/1/Letter Aa (5).pptx",
              },
              {
                title: "Letter Aa (8).pdf",
                file: "uploads/lessons/1/Letter Aa (8).pdf",
              },
              {
                title: "Letter Bb (5).pdf",
                file: "uploads/lessons/1/Letter Bb (5).pdf",
              },
            ],
          },

          {
            title: "Lesson 6_Letter_Cc_DD",
            files: [
              {
                title: "Letter Cc.pdf",
                file: "uploads/lessons/6/Letter Cc.pdf",
              },
              {
                title: "Letter Cc.pptx",
                file: "uploads/lessons/6/Letter Cc.pptx",
              },
              {
                title: "Letter D,d.pptx",
                file: "uploads/lessons/6/Letter D,d.pptx",
              },
              {
                title: "Letter Dd.pdf",
                file: "uploads/lessons/6/Letter Dd.pdf",
              },
            ],
          },
        ],
      },
    ],
  },
];
