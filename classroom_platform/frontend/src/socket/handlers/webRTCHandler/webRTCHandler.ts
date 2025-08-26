import type { Socket } from "socket.io-client";
import { WEB_RTC_HANDLER_ACTIONS as ACTIONS } from "./webRTCActions";
import { useWebRTCStore } from "@/store/webRTC/store";
import freeice from "freeice";
import isNull from "lodash.isnull";
import { WEB_RTC_STORE_PEER_MEDIA_ELEMENTS } from "@/store/webRTC/constants";

export class WebRTCHandler {
  private socket: Socket;

  constructor(socket: Socket) {
    this.socket = socket;
  }

  registerHandlers() {
    this.socket.on(ACTIONS.ADD_PEER, (data) => this.handleAddPeer(data));
    this.socket.on(ACTIONS.SESSION_DESCRIPTION, (data) =>
      this.handleRemoteMedia(data),
    );
    this.socket.on(ACTIONS.ICE_CANDIDATE, (data) =>
      this.handleIceCandidate(data),
    );
    this.socket.on(ACTIONS.REMOVE_PEER, (data) => this.handleRemovePeer(data));
  }

  // * Listeners

  private async handleAddPeer({
    peerId,
    createOffer,
  }: {
    peerId: string;
    createOffer: boolean;
  }) {
    const peerConnections = useWebRTCStore.getState().peerConnections;
    const localMediaStream = useWebRTCStore.getState().localMediaStream;
    const peerMediaElements = WEB_RTC_STORE_PEER_MEDIA_ELEMENTS.current;

    const setPeerMediaElement = useWebRTCStore.getState().setPeerMediaElement;
    const setPeerConnection = useWebRTCStore.getState().setPeerConnection;
    const setClient = useWebRTCStore.getState().setClient;

    // if (peerId in peerConnections) {
    //   return console.warn(`Already connected to peer ${peerId}`);
    // }

    const newConnection = new RTCPeerConnection({
      iceServers: freeice(),
    });

    newConnection.onicecandidate = (event) => {
      if (event.candidate) {
        this.socket.emit(ACTIONS.RELAY_ICE, {
          peerId,
          iceCandidate: event.candidate,
        });
      }
    };

    setPeerConnection(peerId, newConnection);

    console.log(peerId, "peerId")
    console.log(newConnection, "new")

    let tracksNumber = 0;

    if (peerConnections[peerId]) {
      console.log("inside add client")
      peerConnections[peerId].ontrack = ({ streams: [remoteStream] }) => {
        tracksNumber++;

        if (tracksNumber === 2) {
          // video & audio tracks received
          tracksNumber = 0;
          setClient(peerId);

          const peerElement = peerMediaElements[peerId];

          if (peerElement) {
            peerElement.srcObject = remoteStream;
            setPeerMediaElement(peerId, peerElement);
          }
        }
      };
    }

    if (localMediaStream) {
      localMediaStream.getTracks().forEach((track) => {
        if (!isNull(peerConnections[peerId])) {
          console.log(peerConnections, "peers")
          const peerConnection = peerConnections[peerId];
          console.log(peerConnection, "peer")
          peerConnection.addTrack(track, localMediaStream);
          setPeerConnection(peerId, peerConnection);
        }
        
      });
    }

    if (createOffer && peerConnections[peerId]) {
      const peerConnection = peerConnections[peerId];
      const offer = await peerConnection.createOffer();

      await peerConnection.setLocalDescription(offer);

      setPeerConnection(peerId, peerConnection);

      this.socket.emit(ACTIONS.RELAY_SDP, {
        peerId,
        sessionDescription: offer,
      });
    }
  }

  private async handleRemoteMedia({
    peerId,
    remoteDescription,
  }: {
    peerId: string;
    remoteDescription: RTCSessionDescriptionInit;
  }) {
    const peerConnections = useWebRTCStore.getState().peerConnections;
    const setPeerConnection = useWebRTCStore.getState().setPeerConnection;
    const peerConnection = peerConnections[peerId];

    if (!isNull(peerConnection)) {
      await peerConnection.setRemoteDescription(
        new RTCSessionDescription(remoteDescription),
      );

      if (remoteDescription.type === "offer") {
        const answer = await peerConnection.createAnswer();

        await peerConnection.setLocalDescription(answer);

        setPeerConnection(peerId, peerConnection);

        this.socket.emit(ACTIONS.RELAY_SDP, {
          peerId,
          sessionDescription: answer,
        });
      }
    }
  }

  private handleIceCandidate({
    peerId,
    iceCandidate,
  }: {
    peerId: string;
    iceCandidate: RTCLocalIceCandidateInit;
  }) {
    const peerConnections = useWebRTCStore.getState().peerConnections;
    const setPeerConnection = useWebRTCStore.getState().setPeerConnection;
    const peerConnection = peerConnections[peerId];

    if (!isNull(peerConnection)) {
      peerConnection.addIceCandidate(new RTCIceCandidate(iceCandidate));

      setPeerConnection(peerId, peerConnection);
    }
  }

  private handleRemovePeer({ peerId }: { peerId: string }) {
    const peerConnections = useWebRTCStore.getState().peerConnections;
    const peerConnection = peerConnections[peerId];
    const removePeerConnection = useWebRTCStore.getState().removePeerConnection;
    const removePeerMediaElement =
      useWebRTCStore.getState().removePeerMediaElement;
    const removeClient = useWebRTCStore.getState().removeClient;

    if (!isNull(peerConnection)) {
      peerConnection.close();

      removePeerConnection(peerId);
    }

    removePeerMediaElement(peerId);
    removeClient(peerId);
  }

  // * Emitters
}
