import { useState, type ReactNode } from "react";
import {
  FaFolder,
  FaFolderOpen,
  FaChevronRight,
  FaChevronDown,
} from "react-icons/fa";
import "./style.scss";

type T_Props = {
  label: string;
  children?: ReactNode;
};

export function FolderItem({ label, children }: T_Props) {
  const [open, setOpen] = useState(false);

  const toggle = () => {
    if (children) setOpen((prev) => !prev);
  };

  return (
    <div className="folder-item">
      <div
        className={`folder-header ${children ? "clickable" : ""}`}
        onClick={toggle}
      >
        {children ? (
          open ? (
            <FaChevronDown className="chevron" />
          ) : (
            <FaChevronRight className="chevron" />
          )
        ) : (
          <span className="chevron-placeholder" />
        )}

        {open ? (
          <FaFolderOpen className="folder-icon open" />
        ) : (
          <FaFolder className="folder-icon" />
        )}

        <span className="folder-label">{label}</span>
      </div>

      {open && children && <div className="folder-children">{children}</div>}
    </div>
  );
}

export default FolderItem;
