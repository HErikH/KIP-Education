export const WEB_RTC_HANDLER_ACTIONS = {
  ADD_PEER: "peer:add",
  REMOVE_PEER: "peer:remove",
  RELAY_SDP: "sdp:relay",
  RELAY_ICE: "ice:relay",
  ICE_CANDIDATE: "ice:candidate",
  SESSION_DESCRIPTION: "session:description",
} as const;
