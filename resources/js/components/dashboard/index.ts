/**
 * Dashboard Components
 * 
 * Bento Grid dashboard components with neo-brutalism styling.
 * These components are used by AdminDashboard and MemberDashboard.
 */

// Components will be added as they are implemented
export { default as BentoGrid } from './BentoGrid.vue'
export { default as BentoGridItem } from './BentoGridItem.vue'
export { default as StatCard } from './StatCard.vue'
// export { default as DeadlineAlertCard } from './DeadlineAlertCard.vue'
// export { default as ChartCard } from './ChartCard.vue'
// export { default as TeamPerformanceCard } from './TeamPerformanceCard.vue'
// export { default as TaskListCard } from './TaskListCard.vue'
// export { default as ErrorState } from './ErrorState.vue'

// Re-export types for convenience
export type {
  // Core Entities
  Client,
  Product,
  User,
  // Task Types
  TaskStatus,
  TaskPriority,
  Task,
  DeadlineTask,
  // Statistics Types
  AdminDashboardStats,
  MemberDashboardStats,
  // Chart Types
  ChartAreaData,
  ChartDonutData,
  // Team Performance Types
  TeamPerformance,
  // Dashboard Props Types
  AdminDashboardProps,
  MemberDashboardProps,
  // Component Props Types
  ColorTheme,
  AlertType,
  TaskListVariant,
  ChartType,
  GridColumns,
  GridSpan,
} from '@/types/dashboard';

export {
  STATUS_BADGE_COLORS,
  PRIORITY_BADGE_COLORS,
  ALERT_TYPE_COLORS,
} from '@/types/dashboard';
