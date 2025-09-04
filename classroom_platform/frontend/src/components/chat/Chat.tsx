import { useEffect, useRef, useState } from "react";
import { Message } from "../message/Message";
import { ChatInput } from "../chatInput/ChatInput";
import {
  useChatError,
  useChatIsConnected,
  useChatIsLoading,
  useChatMessages,
  useChatSocketId,
} from "@/store/chat/selectors";
import { setChatError } from "@/store/chat/actions";
import clsx from "clsx";
import { MdOutlineChat } from "react-icons/md";
import { PiChatSlashFill } from "react-icons/pi";
import "./style.scss";

export function Chat() {
  const [isVisible, setIsVisible] = useState(false);

  const messagesEndRef = useRef<HTMLDivElement>(null);
  const messagesContainerRef = useRef<HTMLDivElement>(null);

  const socketId = useChatSocketId();
  const messages = useChatMessages();
  const isConnected = useChatIsConnected();
  const isLoading = useChatIsLoading();
  const error = useChatError();

  const setError = setChatError();

  // Auto-scroll to bottom when new messages arrive
  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
  };

  useEffect(() => {
    scrollToBottom();
  }, [messages]);

  // Auto-clear errors after 5 seconds
  useEffect(() => {
    if (error) {
      const timeout = setTimeout(() => {
        setError({ error: null });
      }, 5000);
      return () => clearTimeout(timeout);
    }
  }, [error]);

  return (
    <>
      <div className={clsx("chat", { "chat--hidden": !isVisible })}>
        <div className="chat__header">
          <h3 className="chat__title">Chat</h3>
          <div className="chat__status">
            <span
              className={`chat__status-indicator ${isConnected ? "chat__status-indicator--connected" : "chat__status-indicator--disconnected"}`}
            >
              {isConnected ? "Connected" : "Disconnected"}
            </span>
          </div>
        </div>

        <div className="chat__body">
          {error && (
            <div className="chat__error">
              <span className="chat__error-message">{error}</span>
              <button
                className="chat__error-close"
                onClick={() => setError({ error: null })}
                type="button"
              >
                ×
              </button>
            </div>
          )}

          <div ref={messagesContainerRef} className="chat__messages">
            {isLoading && messages.length === 0 && (
              <div className="chat__loading">Loading chat history...</div>
            )}

            {!isLoading && messages.length === 0 && (
              <div className="chat__empty">
                No messages yet. Start the conversation!
              </div>
            )}

            {messages.map((message) => (
              <Message
                key={message.messageId}
                message={message}
                isOwn={message.senderId === socketId}
              />
            ))}

            <div ref={messagesEndRef} />
          </div>
        </div>

        <ChatInput
          disabled={!isConnected || isLoading}
          placeholder={!isConnected ? "Connecting..." : "Type a message..."}
        />
      </div>

      <button
        className="collapse-chat"
        type="button"
        onClick={() => setIsVisible(!isVisible)}
      >
        {isVisible ? <PiChatSlashFill /> : <MdOutlineChat />}
      </button>
    </>
  );
}
