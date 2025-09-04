export function validateMessage(content) {
  if (!content || typeof content !== "string") {
    return {
      valid: false,
      error: "Message content is required and must be a string",
    };
  }

  const trimmedContent = content.trim();

  if (trimmedContent.length === 0) {
    return { valid: false, error: "Message cannot be empty" };
  }

  if (trimmedContent.length > 1000) {
    return { valid: false, error: "Message too long (max 1000 characters)" };
  }

  if (trimmedContent.length < 1) {
    return { valid: false, error: "Message too short (min 1 character)" };
  }

  // Basic sanitization - remove potential harmful characters
  const sanitized = trimmedContent
    .replace(/[\x00-\x1F\x7F]/g, "") // Remove control characters
    .trim();

  return {
    valid: true,
    content: sanitized,
  };
}

export function validateRoomId(roomId) {
  if (!roomId || typeof roomId !== "string") {
    return { valid: false, error: "Room ID is required and must be a string" };
  }

  const trimmedRoomId = roomId.trim();

  if (trimmedRoomId.length === 0) {
    return { valid: false, error: "Room ID cannot be empty" };
  }

  return {
    valid: true,
    roomId: trimmedRoomId,
  };
}
