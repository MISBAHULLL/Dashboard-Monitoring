import { Sidebar } from "./components/Sidebar";

export default function App() {
  return (
    <div className="min-h-screen bg-gray-100 flex items-start justify-center gap-8 p-8">
      {/* Sidebar – full nav with text labels */}
      <Sidebar />
    </div>
  );
}
