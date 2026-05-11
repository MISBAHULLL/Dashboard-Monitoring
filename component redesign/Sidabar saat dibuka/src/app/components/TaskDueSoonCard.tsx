import { Clock } from "lucide-react";

export function TaskDueSoonCard() {
  return (
    <div
      className="relative bg-white rounded-[18px] w-full"
      style={{
        border: "2.5px solid #111",
        boxShadow: "0 6px 0 0 #FAA700, 0 8px 0 0 #111",
      }}
    >
      {/* Header Row */}
      <div className="flex items-center justify-between px-6 pt-5 pb-2">
        {/* Left: icon + title */}
        <div className="flex items-center gap-3">
          <Clock
            size={34}
            strokeWidth={2}
            className="text-[#0b2a6b] flex-shrink-0"
          />
          <h2
            className="text-[#0b2a6b] whitespace-nowrap"
            style={{
              fontFamily: "'Solway', sans-serif",
              fontSize: "28px",
              fontWeight: 700,
              lineHeight: "normal",
            }}
          >
            Task Due Soon (H -7)
          </h2>
        </div>

        {/* Right: View All button */}
        <button
          className="flex items-center gap-1 border-2 border-black rounded-full px-4 py-1 bg-white hover:bg-gray-50 transition-colors cursor-pointer"
          style={{
            fontFamily: "'Solway', sans-serif",
            fontSize: "14px",
            fontWeight: 500,
            lineHeight: "normal",
            color: "#111",
          }}
        >
          View All
          <span className="text-base">→</span>
        </button>
      </div>

      {/* Divider */}
      <div className="mx-6 border-t border-gray-100" />

      {/* Body */}
      <div className="flex flex-col items-center justify-center py-10 gap-3">
        {/* Big zero */}
        <span
          style={{
            fontFamily: "'Solway', sans-serif",
            fontSize: "72px",
            fontWeight: 700,
            color: "#FAA700",
            lineHeight: 1,
          }}
        >
          0
        </span>

        {/* Subtitle */}
        <p
          style={{
            fontFamily: "'Solway', sans-serif",
            fontSize: "15px",
            fontWeight: 400,
            color: "#6b7280",
            lineHeight: "normal",
          }}
        >
          Tidak ada task dus soon.
        </p>
      </div>
    </div>
  );
}
