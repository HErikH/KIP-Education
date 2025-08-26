import { useState, useCallback, useRef, useEffect } from "react";

type T_Func<T> = (param: T) => void;
type T_Return<T> = [T, (newState: T_Func<T> | T, cb: T_Func<T>) => void];

export function useStateWithCallback<Type>(initialState: Type): T_Return<Type> {
  const [state, setState] = useState<Type>(initialState);
  const cbRef = useRef<T_Func<Type> | null>(null);

  const updateState = useCallback(
    (newState: T_Func<Type> | Type, cb: T_Func<Type>) => {
      cbRef.current = cb;

      setState((prev: Type) =>
        typeof newState === "function"
          ? (newState as (prev: Type) => Type)(prev)
          : newState,
      );
    },
    [],
  );

  useEffect(() => {
    if (cbRef.current) {
      cbRef.current(state);
      cbRef.current = null;
    }
  }, [state]);

  return [state, updateState];
}
