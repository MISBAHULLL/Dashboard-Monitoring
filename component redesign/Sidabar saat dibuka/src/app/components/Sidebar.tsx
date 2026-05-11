import imgLogo from "figma:asset/da9c4a154bb6cdb9dba296573bcd0c483165f2cf.png";
import svgPaths from "../../imports/Group158/svg-ln6aq177r4";
import { ChevronsUpDown } from "lucide-react";

// ─── Icon helpers ─────────────────────────────────────────────────────────────

function IconDashboard() {
  return (
    <svg width="32" height="32" viewBox="0 0 51.5 51.5" fill="none" aria-hidden="true" style={{ flexShrink: 0 }}>
      <path
        d={svgPaths.p384f7f00}
        stroke="white"
        strokeLinecap="round"
        strokeLinejoin="round"
        strokeWidth="1.5"
      />
    </svg>
  );
}

function IconTabelTask() {
  return (
    <svg width="32" height="32" viewBox="0 0 50 50" fill="none" aria-hidden="true" style={{ flexShrink: 0 }}>
      <path d={svgPaths.p2a72e480} fill="white" />
    </svg>
  );
}

function IconKanban() {
  return (
    <svg width="32" height="32" viewBox="0 0 50 50" fill="none" aria-hidden="true" style={{ flexShrink: 0 }}>
      <path d={svgPaths.p3a02e600} fill="white" />
    </svg>
  );
}

function IconAuditTrail() {
  return (
    <svg width="32" height="32" viewBox="0 0 52 52" fill="none" aria-hidden="true" style={{ flexShrink: 0 }}>
      <path d={svgPaths.p12408470} stroke="white" />
      <path
        d={svgPaths.p121fad80}
        stroke="white"
        strokeLinecap="square"
        strokeWidth="2"
      />
    </svg>
  );
}

function IconDocument() {
  return (
    <svg width="32" height="32" viewBox="0 0 50 50" fill="none" aria-hidden="true" style={{ flexShrink: 0 }}>
      <path d={svgPaths.p92f6000} fill="white" />
    </svg>
  );
}

function IconFaskes() {
  return (
    <svg width="32" height="32" viewBox="0 0 50 50" fill="none" aria-hidden="true" style={{ flexShrink: 0 }}>
      <path
        d={svgPaths.p3c8ce00}
        fill="white"
        clipRule="evenodd"
        fillRule="evenodd"
      />
    </svg>
  );
}

function IconTeam() {
  return (
    <svg width="32" height="32" viewBox="0 0 50 50" fill="none" aria-hidden="true" style={{ flexShrink: 0 }}>
      <path d={svgPaths.p15d0a200} fill="white" />
    </svg>
  );
}

function IconUser() {
  return (
    <svg width="32" height="32" viewBox="0 0 50 50" fill="none" aria-hidden="true" style={{ flexShrink: 0 }}>
      <path
        d={svgPaths.p2f1d4300}
        fill="white"
        clipRule="evenodd"
        fillRule="evenodd"
      />
    </svg>
  );
}

function IconSettings() {
  return (
    <svg width="32" height="32" viewBox="0 0 51 51.0002" fill="none" aria-hidden="true" style={{ flexShrink: 0 }}>
      <path
        d={svgPaths.p38ac5f00}
        stroke="white"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
    </svg>
  );
}

function IconAdminUser() {
  return (
    <svg width="32" height="32" viewBox="0 0 50 50" fill="none" aria-hidden="true" style={{ flexShrink: 0 }}>
      <path
        d={svgPaths.p22ebfc80}
        fill="white"
        clipRule="evenodd"
        fillRule="evenodd"
      />
      <path
        d={svgPaths.p17bcd980}
        fill="white"
        clipRule="evenodd"
        fillRule="evenodd"
      />
    </svg>
  );
}

// ─── Nav item ─────────────────────────────────────────────────────────────────

interface NavItemProps {
  icon: React.ReactNode;
  label: string;
}

function NavItem({ icon, label }: NavItemProps) {
  return (
    <li>
      <button
        type="button"
        style={{
          display: "flex",
          alignItems: "center",
          gap: "14px",
          width: "100%",
          padding: "10px 16px",
          background: "none",
          border: "none",
          cursor: "pointer",
          borderRadius: "12px",
          textAlign: "left",
          color: "white",
        }}
        onMouseEnter={(e) =>
          ((e.currentTarget as HTMLButtonElement).style.background =
            "rgba(255,255,255,0.08)")
        }
        onMouseLeave={(e) =>
          ((e.currentTarget as HTMLButtonElement).style.background = "none")
        }
      >
        {icon}
        <span
          style={{
            fontFamily: "'Plus Jakarta Sans', sans-serif",
            fontWeight: "600",
            fontSize: "22px",
            color: "white",
            lineHeight: 1.3,
          }}
        >
          {label}
        </span>
      </button>
    </li>
  );
}

// ─── Main Sidebar ─────────────────────────────────────────────────────────────

const NAV_ITEMS: NavItemProps[] = [
  { icon: <IconDashboard />,  label: "Dashboard" },
  { icon: <IconTabelTask />,  label: "Tabel Task" },
  { icon: <IconKanban />,     label: "Kanban Board" },
  { icon: <IconAuditTrail />, label: "Audit Trail" },
  { icon: <IconDocument />,   label: "Document" },
  { icon: <IconFaskes />,     label: "Faskes / Client" },
  { icon: <IconTeam />,       label: "Team" },
  { icon: <IconUser />,       label: "User" },
];

export function Sidebar() {
  return (
    <nav
      aria-label="Main navigation"
      style={{
        width: "375px",
        minHeight: "980px",
        backgroundColor: "#093b70",
        borderRadius: "51px",
        display: "flex",
        flexDirection: "column",
        padding: "0 0 32px 0",
        flexShrink: 0,
        position: "relative",
        overflow: "hidden",
      }}
    >
      {/* ── Logo ─────────────────────────────────────────── */}
      <div
        style={{
          paddingLeft: "18px",
          paddingTop: "20px",
          paddingBottom: "14px",
          flexShrink: 0,
        }}
      >
        <img
          src={imgLogo}
          alt="trustmedis – healthtech solution"
          style={{
            width: "142px",
            height: "142px",
            objectFit: "contain",
            display: "block",
          }}
        />
      </div>

      {/* ── Navigation list ──────────────────────────────── */}
      <ul
        style={{
          listStyle: "none",
          margin: "0",
          padding: "0 18px",
          display: "flex",
          flexDirection: "column",
          gap: "4px",
        }}
      >
        {NAV_ITEMS.map((item) => (
          <NavItem key={item.label} icon={item.icon} label={item.label} />
        ))}
      </ul>

      {/* ── Spacer ───────────────────────────────────────── */}
      <div style={{ flex: 1 }} />

      {/* ── Bottom section ───────────────────────────────── */}
      <div
        style={{
          padding: "0 18px",
          display: "flex",
          flexDirection: "column",
          gap: "4px",
        }}
      >
        {/* Pengaturan Sistem */}
        <NavItem icon={<IconSettings />} label="Pengaturan Sistem" />

        {/* Admin PO with selector chevron */}
        <button
          type="button"
          style={{
            display: "flex",
            alignItems: "center",
            gap: "14px",
            width: "100%",
            padding: "10px 16px",
            background: "none",
            border: "none",
            cursor: "pointer",
            borderRadius: "12px",
            color: "white",
          }}
          onMouseEnter={(e) =>
            ((e.currentTarget as HTMLButtonElement).style.background =
              "rgba(255,255,255,0.08)")
          }
          onMouseLeave={(e) =>
            ((e.currentTarget as HTMLButtonElement).style.background = "none")
          }
        >
          <IconAdminUser />
          <span
            style={{
              fontFamily: "'Plus Jakarta Sans', sans-serif",
              fontWeight: "600",
              fontSize: "22px",
              color: "white",
              lineHeight: 1.3,
              flex: 1,
              textAlign: "left",
            }}
          >
            Admin PO
          </span>
          <ChevronsUpDown
            size={26}
            color="white"
            strokeWidth={2.2}
            style={{ flexShrink: 0 }}
          />
        </button>
      </div>
    </nav>
  );
}
