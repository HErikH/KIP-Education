import { useState } from "react";
import Modal from "react-responsive-modal";
import { FaUsers } from "react-icons/fa";
import { useRoom } from "@/hooks/useRooms";
import { HiMiniHandRaised } from "react-icons/hi2";
import Tooltip from "rc-tooltip";
import { createPortal } from "react-dom";
import "./style.scss";

export function ParticipantsList() {
  const [showParticipantsList, setShowParticipantsList] = useState(false);
  const { raisedHands, raiseHand, lowerHand, userRole, userId } = useRoom();

  return userRole === "teacher" ? (
    <>
      <Modal
        open={showParticipantsList}
        onClose={() => setShowParticipantsList(false)}
        styles={{
          modal: {
            padding: "5rem",
            borderRadius: "0.8rem",
          },
        }}
        center
      >
        {raisedHands.length > 1 ? (
          raisedHands.map((user) => {
            if (user.userId !== userId) {
              return (
                <div className="participants-list">
                  <p>
                    {user.username}: {user.role}
                  </p>
                  <Tooltip
                    placement="top"
                    showArrow={false}
                    align={{
                      offset: [0, -15],
                    }}
                    overlay={
                      <p className="participants-list__tooltip-overlay">
                        {user.raised ? "Lower" : "Raise"} Hand
                      </p>
                    }
                  >
                    <button
                      className="participants-list__button"
                      onClick={() =>
                        user.raised
                          ? lowerHand(user.userId, user.username)
                          : raiseHand(user.userId, user.username)
                      }
                    >
                      <HiMiniHandRaised
                        color={user.raised ? "red" : "springgreen"}
                        className="participants-list__icon"
                      />
                    </button>
                  </Tooltip>
                </div>
              );
            }
          })
        ) : (
          <p className="participants-list__no-users">
            There are no students in the room !
          </p>
        )}
      </Modal>

      {createPortal(
        <button
          className="collapse-participants controllers-aside__item"
          type="button"
          onClick={() => setShowParticipantsList(!showParticipantsList)}
        >
          <FaUsers />
        </button>,
        document.getElementById("controllers-aside")!,
      )}
    </>
  ) : null;
}
