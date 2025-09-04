import type { T_Message } from "../../store/chat/types";
import { formatTimestamp } from "@/helpers/functions/message";
import clsx from "clsx";
import "./style.scss";

type T_MessageProps = {
  message: T_Message;
  isOwn: boolean;
};

export function Message({ message, isOwn }: T_MessageProps) {
  return (
    <div
      className={clsx("message", {
        "message--system":
          message.type === "system" ||
          message.type === "join" ||
          message.type === "leave",
        "message--own": isOwn,
        "message--other":
          !isOwn && !["system", "join", "leave"].includes(message.type),
      })}
    >
      {message.type === "system" ||
      message.type === "join" ||
      message.type === "leave" ? (
        <div className="message__content message__content--system">
          {message.content}
        </div>
      ) : (
        <>
          <div className="message__header">
            <span className="message__sender">
              {isOwn ? "You" : message.senderId}
              {message.senderRole && (
                <span className="message__role"> ({message.senderRole})</span>
              )}
            </span>
            <span className="message__timestamp">
              {formatTimestamp(message.timestamp)}
            </span>
          </div>
          <div className="message__content">{message.content}</div>
        </>
      )}
    </div>
  );
}
