/// <reference types="vite/client" />
declare module "freeice" {
  interface FreeICEOptions {
    stunServers?: number;
  }

  function freeice(options?: FreeICEOptions): RTCIceServer[];

  export default freeice;
}