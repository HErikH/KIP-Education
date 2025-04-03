import React from "react";

const LessonCard = ({ lesson, onStart, onRestart }) => {
  return (
    <div className={`post-card ${lesson.active === 1 ? 'active' : ''}`}>
      <img src={lesson.image} alt="Lesson" />
      <h3>{lesson.title}</h3>
      <p>Tag: {lesson.tag}</p>
      <button
        className={`status-button ${lesson.active === 1 ? 'active' : ''}`}
        onClick={() => (lesson.active === 1 ? onRestart(lesson.id) : onStart(lesson.id))}
      >
        {lesson.active === 1 ? 'Restart' : 'Start'}
      </button>
    </div>
  );
};

export default LessonCard;
