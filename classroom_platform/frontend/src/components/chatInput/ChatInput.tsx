import React, { useState, type KeyboardEvent } from "react";
import { chatHandler } from "@/socket/socketServer";
import { IoIosSend } from "react-icons/io";
import "./style.scss";

type T_ChatInputProps = {
  disabled?: boolean;
  placeholder?: string;
};

export function ChatInput({
  disabled = false,
  placeholder = "Type a message...",
}: T_ChatInputProps) {
  const [message, setMessage] = useState("");

  const handleSend = () => {
    const trimmedMessage = message.trim();
    if (trimmedMessage && !disabled) {
      chatHandler.sendMessage(trimmedMessage);
      setMessage("");
    }
  };

  const handleKeyPress = (e: KeyboardEvent<HTMLInputElement>) => {
    if (e.key === "Enter" && !e.shiftKey) {
      e.preventDefault();
      handleSend();
    }
  };

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const value = e.target.value;
    // Basic validation - max 1000 characters
    if (value.length <= 1000) {
      setMessage(value);
    }
  };

  return (
    <div className="chat-input">
      <div className="chat-input__container">
        <input
          name="message"
          type="text"
          className="chat-input__field"
          value={message}
          onChange={handleInputChange}
          onKeyPress={handleKeyPress}
          placeholder={disabled ? "Connecting..." : placeholder}
          disabled={disabled}
          maxLength={1000}
        />
        <button
          className="chat-input__button"
          onClick={handleSend}
          disabled={disabled || !message.trim()}
          type="button"
        >
          Send
          <IoIosSend />
        </button>
      </div>
      {message.length > 900 && (
        <div className="chat-input__counter">{message.length}/1000</div>
      )}
    </div>
  );
}
