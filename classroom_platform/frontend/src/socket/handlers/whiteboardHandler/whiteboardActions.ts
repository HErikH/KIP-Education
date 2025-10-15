export const WHITEBOARD_ACTIONS = {
  // Drawing actions
  START_DRAWING: "whiteboard:start-drawing",
  DRAW: "whiteboard:draw",
  STOP_DRAWING: "whiteboard:stop-drawing",
  CLEAR_CANVAS: "whiteboard:clear-canvas",
  UNDO: "whiteboard:undo",
  REDO: "whiteboard:redo",

  // File actions
  UPLOAD_FILE: "whiteboard:upload-file",
  FILE_UPLOADED: "whiteboard:file-uploaded",
  LOAD_FILE: "whiteboard:load-file",
  DELETE_FILE: "whiteboard:delete-file",
  REMOVE_FILE: "whiteboard:remove-file",
  SET_CURRENT_FILE: "whiteboard:set-current-file",

  // Room actions
  JOIN_ROOM: "whiteboard:join-room",
  LEAVE_ROOM: "whiteboard:leave-room",
  USER_JOINED: "whiteboard:user-joined",
  USER_LEFT: "whiteboard:user-left",

  // Sync actions
  REQUEST_SYNC: "whiteboard:request-sync",
  SYNC_STATE: "whiteboard:sync-state",
} as const;
