import React, { useEffect, useState } from "react";
import LessonCard from "./LessonCard";
import axios from "axios";

const LessonsList = () => {
  const [lessons, setLessons] = useState([]);

  useEffect(() => {
    // Fetch lessons when the component loads
    axios.get("http://localhost/lessons_api.php").then((response) => {
      setLessons(response.data);
    });
  }, []);

  const handleStart = (id) => {
    axios.post("http://localhost/lessons_api.php", { lesson_id: id, start_lesson: true })
      .then(() => {
        setLessons(
          lessons.map((lesson) =>
            lesson.id === id ? { ...lesson, active: 1 } : lesson
          )
        );
      });
  };

  const handleRestart = (id) => {
    axios.post("http://localhost/lessons_api.php", { lesson_id: id, restart_lesson: true })
      .then(() => {
        setLessons(
          lessons.map((lesson) =>
            lesson.id === id ? { ...lesson, active: 0 } : lesson
          )
        );
      });
  };

  return (
    <div className="posts-section">
      {lessons.map((lesson) => (
        <LessonCard
          key={lesson.id}
          lesson={lesson}
          onStart={handleStart}
          onRestart={handleRestart}
        />
      ))}
    </div>
  );
};

export default LessonsList;
