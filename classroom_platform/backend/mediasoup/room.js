import { Peer } from "./peer.js";

export class Room {
  constructor(id, router) {
    this.id = id;
    this.router = router;
    this.peers = new Map(); // socketId -> Peer instance
  }

  addPeer(socketId) {
    if (this.peers.has(socketId)) {
      return this.peers.get(socketId);
    }

    const peer = new Peer(socketId, this.router);
    this.peers.set(socketId, peer);

    console.log(`👤 Added peer ${socketId} to room ${this.id}`);

    return peer;
  }

  removePeer(socketId) {
    const peer = this.peers.get(socketId);

    if (peer) {
      peer.close();
      this.peers.delete(socketId);

      console.log(`👋 Removed peer ${socketId} from room ${this.id}`);
    }
  }

  getPeer(socketId) {
    return this.peers.get(socketId);
  }

  getPeerIds() {
    return Array.from(this.peers.keys());
  }

  isEmpty() {
    return this.peers.size === 0;
  }

  close() {
    for (const peer of this.peers.values()) {
      peer.close();
    }

    this.peers.clear();
  }
}
