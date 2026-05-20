/**
 * Dashboard Type Definitions
 * 
 * TypeScript interfaces for all data models used in the Bento Grid dashboard components.
 * These types align with the data structures returned by DashboardController.
 * 
 * @see App\Http\Controllers\DashboardController
 */

// ============================================================================
// Core Entities
// ============================================================================

/**
 * Client/Faskes entity
 */
export interface Client {
  id?: number;
  name: string;
}

/**
 * Product/Team entity
 */
export interface Product {
  id: number;
  name: string;
}

/**
 * User/Assignee entity
 */
export interface User {
  id: number;
  name: string;
}

// ============================================================================
// Task Types
// ============================================================================

/**
 * Valid task status values
 */
export type TaskStatus = 'open' | 'in_progress' | 'revision' | 'completed';

/**
 * Valid task priority values
 */
export type TaskPriority = 'urgent' | 'high' | 'medium' | 'low';

/**
 * Base task interface shared across dashboards
 */
export interface Task {
  id: number;
  title: string;
  modul?: string;
  status: TaskStatus;
  priority?: TaskPriority;
  client?: Client;
  product?: Product;
  assignee?: User;
  release_date?: string;
  sla_due_date?: string;
  sla_warning_date?: string;
  created_at: string;
}

/**
 * Task data for deadline alert cards (overdue/due soon)
 */
export type DeadlineTask = Pick<Task, 'id' | 'title' | 'client' | 'release_date' | 'sla_due_date'>;

// ============================================================================
// Statistics Types
// ============================================================================

/**
 * Admin dashboard statistics
 */
export interface AdminDashboardStats {
  total_tasks: number;
  active_tasks: number;
  trashed_tasks: number;
  total_tasks_with_trashed: number;
  open_tasks: number;
  in_progress_tasks: number;
  completed_tasks: number;
  total_clients: number;
  total_teams: number;
}

/**
 * Member dashboard statistics
 */
export interface MemberDashboardStats {
  total_tasks: number;
  open_tasks: number;
  in_progress_tasks: number;
  completed_tasks: number;
  overdue_tasks?: number;
  due_soon_tasks?: number;
}

// ============================================================================
// Chart Types
// ============================================================================

/**
 * Area chart data structure
 * Shows task creation trends for the last 7 days
 */
export interface ChartAreaData {
  categories: string[];
  data: number[];
}

/**
 * Donut chart data structure
 * Shows task status distribution [open, in_progress, revision, completed]
 */
export type ChartDonutData = number[];

/**
 * Dashboard trend deltas for admin summary cards
 */
export interface DashboardTrends {
  tasks: number;
  teams: number;
  pending: number;
  clients: number;
}

export type DashboardPeriod = '7d' | '30d' | 'month' | 'all';

export interface DashboardPeriodOption {
  value: DashboardPeriod;
  label: string;
}

// ============================================================================
// Team Performance Types
// ============================================================================

/**
 * Team performance metrics for dashboard display
 */
export interface TeamPerformance {
  id: number;
  name: string;
  type?: string;
  total_tasks: number;
  completed_tasks: number;
  open_tasks: number;
  in_progress_tasks: number;
  revision_tasks: number;
  overdue_tasks: number;
  completion_rate: number;
}

// ============================================================================
// Dashboard Props Types
// ============================================================================

/**
 * Props passed to AdminDashboard from DashboardController
 */
export interface AdminDashboardProps {
  stats: AdminDashboardStats;
  trends: DashboardTrends;
  chart_donut: ChartDonutData;
  chart_area: ChartAreaData;
  chart_month: ChartAreaData;
  overdue_count: number;
  due_soon_count: number;
  overdue_tasks: DeadlineTask[];
  due_soon_tasks: DeadlineTask[];
  team_performance: TeamPerformance[];
  recent_tasks: Task[];
  dashboard_period: DashboardPeriod;
  dashboard_period_label: string;
  dashboard_period_options: DashboardPeriodOption[];
}

/**
 * Props passed to MemberDashboard from DashboardController
 */
export interface MemberDashboardProps {
  stats: MemberDashboardStats;
  my_tasks: Task[];
  overdue_tasks?: DeadlineTask[];
  due_soon_tasks?: DeadlineTask[];
}

// ============================================================================
// Component Props Types
// ============================================================================

/**
 * Color theme options for StatCard and other components
 */
export type ColorTheme = 'navy' | 'green' | 'red' | 'amber' | 'neutral';

/**
 * Alert type for DeadlineAlertCard
 */
export type AlertType = 'overdue' | 'due_soon';

/**
 * Task list variant for TaskListCard
 */
export type TaskListVariant = 'recent' | 'assigned';

/**
 * Supported chart types for ChartCard
 */
export type ChartType = 'area' | 'donut';

/**
 * BentoGrid column configuration
 */
export interface GridColumns {
  default: number;
  md?: number;
  lg?: number;
  xl?: number;
}

/**
 * BentoGridItem span configuration
 */
export interface GridSpan {
  default: string;
  md?: string;
  lg?: string;
}

// ============================================================================
// Color Mappings (for property-based testing)
// ============================================================================

/**
 * Status to badge color mapping
 */
export const STATUS_BADGE_COLORS: Record<TaskStatus, string> = {
  open: 'amber',
  in_progress: 'blue',
  revision: 'red',
  completed: 'green',
} as const;

/**
 * Priority to badge color mapping
 */
export const PRIORITY_BADGE_COLORS: Record<TaskPriority, string> = {
  urgent: 'red',
  high: 'amber',
  medium: 'blue',
  low: 'slate',
} as const;

/**
 * Alert type to color mapping
 */
export const ALERT_TYPE_COLORS: Record<AlertType, string> = {
  overdue: 'red',
  due_soon: 'amber',
} as const;
