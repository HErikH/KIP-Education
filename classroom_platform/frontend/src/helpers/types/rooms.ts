export type T_RoomInfo = {
  room_id: string;
  user_id: number;
  class_id: string;
  room_name: string;
  username: string;
  role: "teacher" | "student";
};

export type T_RaiseHand = {
  roomId: string;
  userId: number;
  username: string;
  raised: boolean;
};