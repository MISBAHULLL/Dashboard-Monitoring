# Implementation Plan: Dashboard Bento Grid Redesign

## Overview

This implementation plan transforms the existing AdminDashboard and MemberDashboard into a modern Bento Grid layout with subtle neo-brutalism styling. The work involves creating 8 reusable Vue components, updating both dashboard pages, and implementing comprehensive testing including property-based tests for universal correctness properties.

**Key Principles:**
- No backend changes required - all existing data flow from DashboardController is preserved
- Components are reusable and accept props for flexibility
- Subtle neo-brutalism: bold borders, mild shadows, professional healthcare aesthetic
- Full responsive support (mobile/tablet/desktop)
- Trustmedis brand colors with proper contrast ratios

---

## Tasks

### Phase 1: Foundation Setup

- [x] 1. Create component directory structure and base types
  - Create `resources/js/components/dashboard/` directory
  - Define TypeScript interfaces for all data models (Task, TeamPerformance, etc.)
  - Create shared type definitions file
  - _Requirements: 13.1, 13.3_

- [x] 2. Add TailwindCSS neo-brutalism utility classes
  - Add `.bento-card` base class to `resources/css/app.css`
  - Add color theme variants (`.bento-card-navy`, `.bento-card-green`, `.bento-card-red`, `.bento-card-amber`)
  - Add responsive span utilities (`.bento-span-small`, `.bento-span-medium`, `.bento-span-large`, `.bento-span-full`)
  - Verify dark mode variants are included
  - _Requirements: 2.1, 2.2, 2.3_

### Phase 2: Core Grid Components

- [ ] 3. Implement BentoGrid container component
  - Create `BentoGrid.vue` with CSS Grid layout
  - Accept `columns` prop for responsive breakpoint configuration
  - Accept `gap` prop for spacing control
  - Implement default slot for grid items
  - _Requirements: 1.2, 1.3, 10.1, 10.2, 10.3_

- [ ]* 3.1 Write unit tests for BentoGrid component
  - Test renders with default props
  - Test responsive column classes at different breakpoints
  - Test custom gap classes
  - _Requirements: 1.2, 1.3_

- [ ] 4. Implement BentoGridItem wrapper component
  - Create `BentoGridItem.vue` with span configuration
  - Accept `span` prop with responsive variants (default, md, lg)
  - Apply appropriate col-span-* TailwindCSS classes
  - Support full-width spanning
  - _Requirements: 1.1, 1.4_

- [ ]* 4.1 Write unit tests for BentoGridItem component
  - Test span classes are applied correctly
  - Test responsive span variants
  - Test full-width spanning
  - _Requirements: 1.1_

### Phase 3: StatCard Component

- [ ] 5. Implement StatCard component with neo-brutalism styling
  - Create `StatCard.vue` with props: label, value, icon, colorTheme, loading, animate
  - Implement 5 color themes: navy, green, red, amber, neutral
  - Add 1.5px border and subtle offset shadow
  - Implement hover animation (shadow shift, slight translate)
  - Implement inline loading skeleton with pulse animation
  - Ensure color contrast ratios meet WCAG 4.5:1 minimum
  - Add appropriate ARIA label (`{label}: {value}`)
  - _Requirements: 2.1, 2.2, 2.4, 2.5, 2.6, 3.1-3.6, 8.1-8.5, 12.2, 16.1-16.5_

- [ ]* 5.1 Write unit tests for StatCard component
  - Test renders with all required props
  - Test all 5 color theme variants
  - Test loading skeleton displays when loading=true
  - Test hover classes are applied
  - Test ARIA label format
  - _Requirements: 2.1, 2.3, 12.2_

- [ ]* 5.2 Write property test for StatCard color theme mapping
  - **Property 1: Color Theme Mapping**
  - Generate random colorTheme values and verify component has correct background, border, and text color classes
  - **Validates: Requirements 2.3, 3.3, 3.4, 3.5, 8.2, 8.3, 8.4**

- [ ]* 5.3 Write property test for StatCard ARIA label format
  - **Property 6: ARIA Label Format**
  - Generate StatCard instances with random label and value props
  - Verify aria-label matches expected format "{label}: {value}"
  - **Validates: Requirements 12.2**

### Phase 4: DeadlineAlertCard Component

- [ ] 6. Implement DeadlineAlertCard component
  - Create `DeadlineAlertCard.vue` with props: type, count, tasks, viewAllLink, loading
  - Implement two variants: 'overdue' (red) and 'due_soon' (amber)
  - Display up to 10 tasks with title, client name, release date
  - Show empty state message when no tasks
  - Display "Lihat Semua" link when tasks exceed 10
  - Add inline loading skeleton
  - Use semantic `<article>` element
  - _Requirements: 4.1-4.8, 14.1, 14.3, 16.1-16.5_

- [ ]* 6.1 Write unit tests for DeadlineAlertCard component
  - Test overdue variant renders with red styling
  - Test due_soon variant renders with amber styling
  - Test empty state displays appropriate message
  - Test "Lihat Semua" link appears when tasks > 10
  - Test loading skeleton displays correctly
  - _Requirements: 4.1, 4.2, 4.5, 4.7, 4.8_

- [ ]* 6.2 Write property test for DeadlineAlertCard urgency-to-color mapping
  - **Property 4: Urgency-to-Color Mapping**
  - Generate DeadlineAlertCard with random type values
  - Verify all color elements use correct urgency color (red for overdue, amber for due_soon)
  - **Validates: Requirements 4.1, 4.2, 14.3**

- [ ]* 6.3 Write property test for DeadlineAlertCard task rendering
  - **Property 7: Task List Rendering**
  - Generate random task arrays (0-10 items)
  - Verify each task's title, client name, and release date is present in rendered output
  - **Validates: Requirements 4.3, 4.4**

- [ ]* 6.4 Write property test for "Lihat Semua" link display
  - **Property 8: "Lihat Semua" Link Display**
  - Generate random task arrays with varying lengths
  - Verify link appears only when length > 10
  - **Validates: Requirements 4.7, 4.8**

### Phase 5: ChartCard Component

- [ ] 7. Implement ChartCard component wrapping ApexCharts
  - Create `ChartCard.vue` with props: title, subtitle, chartType, options, series, height, loading
  - Support 'area' and 'donut' chart types
  - Wrap VueApexCharts component with neo-brutalism card styling
  - Add inline loading skeleton for chart area
  - Use semantic `role="img"` with aria-label
  - Preserve all existing ApexCharts configuration
  - _Requirements: 5.1-5.6, 12.4, 16.1-16.5_

- [ ]* 7.1 Write unit tests for ChartCard component
  - Test renders area chart correctly
  - Test renders donut chart correctly
  - Test loading skeleton displays
  - Test aria-label is present
  - _Requirements: 5.1, 5.2, 5.5, 12.4_

- [ ] 7.2 Verify ApexCharts backward compatibility
  - Ensure all existing chart options work with new ChartCard wrapper
  - Verify chart data updates trigger re-renders correctly
  - Test responsive chart resizing
  - _Requirements: 5.5, 11.4_

### Phase 6: TeamPerformanceCard Component

- [ ] 8. Implement TeamPerformanceCard table component
  - Create `TeamPerformanceCard.vue` with props: teams, loading
  - Display table with columns: Team, Total, Selesai, Overdue, Completion Rate
  - Render completion rate as badge with navy styling
  - Display empty state when no teams
  - Add inline loading skeleton
  - Use semantic table elements with proper headers
  - _Requirements: 6.1-6.5, 16.1-16.5_

- [ ]* 8.1 Write unit tests for TeamPerformanceCard component
  - Test renders team data correctly
  - Test completion rate badge displays
  - Test empty state message
  - Test loading skeleton
  - _Requirements: 6.1, 6.3, 6.4_

- [ ]* 8.2 Write property test for team performance ordering
  - **Property 9: Team Performance Ordering**
  - Generate random arrays of team data
  - Verify rendered teams are sorted by total_tasks descending
  - Verify maximum 10 teams displayed
  - **Validates: Requirements 6.5**

### Phase 7: TaskListCard Component

- [ ] 9. Implement TaskListCard component with two variants
  - Create `TaskListCard.vue` with props: variant, tasks, loading
  - Support 'recent' variant (for admin) and 'assigned' variant (for member)
  - Render table with appropriate columns per variant
  - Implement status badges with correct colors (amber=open, blue=in_progress, red=revision, green=completed)
  - Implement priority badges with correct colors (red=urgent, amber=high, blue=medium, slate=low)
  - Display empty state messages per variant
  - Add inline loading skeleton
  - _Requirements: 7.1-7.5, 9.1-9.6, 16.1-16.5_

- [ ]* 9.1 Write unit tests for TaskListCard component
  - Test 'recent' variant renders correct columns
  - Test 'assigned' variant renders correct columns
  - Test status badge color mapping
  - Test priority badge color mapping
  - Test empty state messages
  - _Requirements: 7.2, 7.3, 9.2, 9.3, 9.4_

- [ ]* 9.2 Write property test for status badge color mapping
  - **Property 2: Status Badge Color Mapping**
  - Generate tasks with random status values
  - Verify badge element has correct color class
  - **Validates: Requirements 7.3, 9.4**

- [ ]* 9.3 Write property test for priority badge color mapping
  - **Property 3: Priority Badge Color Mapping**
  - Generate tasks with random priority values
  - Verify badge element has correct color class
  - **Validates: Requirements 9.3**

- [ ]* 9.4 Write property test for task list filtering
  - **Property 5: Task List Filtering**
  - Generate random arrays of tasks with mixed statuses
  - Verify no completed tasks appear in rendered output for 'assigned' variant
  - **Validates: Requirements 9.1**

### Phase 8: ErrorState Component

- [ ] 10. Implement ErrorState component
  - Create `ErrorState.vue` with props: message, onRetry
  - Display error icon with Trustmedis red color
  - Display error message text
  - Render "Coba Lagi" retry button when onRetry provided
  - Maintain component dimensions to prevent layout shift
  - _Requirements: 17.1-17.6_

- [ ]* 10.1 Write unit tests for ErrorState component
  - Test renders error message
  - Test retry button renders when onRetry provided
  - Test retry button is hidden when onRetry not provided
  - Test clicking retry button calls onRetry callback
  - _Requirements: 17.2, 17.3_

### Phase 9: Dashboard Integration

- [ ] 11. Update AdminDashboard.vue to use Bento Grid components
  - Import all new dashboard components
  - Replace existing stat cards with StatCard components in BentoGrid
  - Replace deadline sections with DeadlineAlertCard components
  - Replace chart sections with ChartCard components
  - Replace team performance section with TeamPerformanceCard component
  - Replace recent tasks section with TaskListCard component
  - Configure responsive grid layout per design specification
  - Preserve all existing props from DashboardController
  - Maintain breadcrumb navigation
  - _Requirements: 1.1-1.5, 3.1-3.6, 4.1-4.8, 5.1-5.6, 6.1-6.5, 7.1-7.5, 11.1, 11.3, 11.5_

- [ ]* 11.1 Write integration tests for AdminDashboard data flow
  - Test component receives all props from controller
  - Test all data is rendered in appropriate components
  - Test navigation breadcrumbs remain functional
  - _Requirements: 11.1, 11.5_

- [ ] 12. Update MemberDashboard.vue to use Bento Grid components
  - Import StatCard, TaskListCard, and BentoGrid components
  - Replace existing stat cards with StatCard components
  - Replace task table with TaskListCard component
  - Configure responsive grid layout per design specification
  - Preserve all existing props from DashboardController
  - Maintain breadcrumb navigation
  - _Requirements: 1.1-1.5, 8.1-8.5, 9.1-9.6, 11.2, 11.3, 11.5_

- [ ]* 12.1 Write integration tests for MemberDashboard data flow
  - Test component receives all props from controller
  - Test all data is rendered in appropriate components
  - Test navigation breadcrumbs remain functional
  - _Requirements: 11.2, 11.5_

### Phase 10: Checkpoint - Core Functionality

- [ ] 13. Checkpoint - Verify core functionality and responsive behavior
  - Ensure all unit tests pass
  - Ensure all property tests pass
  - Manually verify responsive grid behavior at mobile (375px), tablet (768px), desktop (1440px)
  - Verify charts render correctly at all breakpoints
  - Verify tables enable horizontal scroll on mobile
  - Ask the user if questions arise.

### Phase 11: Accessibility Verification

- [ ] 14. Implement accessibility features and verify compliance
  - Verify all components use semantic HTML elements (article, section, header)
  - Verify ARIA labels are present on all interactive elements
  - Verify keyboard navigation follows logical reading order
  - Verify color contrast ratios meet WCAG 4.5:1 minimum
  - Add skip links for main content areas
  - _Requirements: 12.1-12.5_

- [ ]* 14.1 Write property test for semantic HTML structure
  - **Property 10: Semantic HTML Structure**
  - For each component type, render and verify root element is article, section, or header
  - **Validates: Requirements 12.1**

- [ ] 14.2 Run accessibility audit
  - Use automated accessibility testing tool (axe-core or similar)
  - Test with screen reader (VoiceOver or NVDA)
  - Document any issues found
  - Verify all WCAG 2.1 Level AA criteria are met
  - _Requirements: 12.1-12.5_

### Phase 12: Visual Regression Testing

- [ ] 15. Set up visual regression testing infrastructure
  - Configure Playwright or Percy for snapshot testing
  - Create baseline snapshots at mobile (375px), tablet (768px), desktop (1440px)
  - Capture dark mode variants for all viewports
  - Set pixel difference threshold to 0.1%
  - _Requirements: 1.1-1.5, 2.1-2.6_

- [ ] 15.1 Capture visual regression baselines for AdminDashboard
  - Capture full dashboard at all viewports
  - Capture individual card variants
  - Capture loading states
  - Capture error states
  - Capture empty states

- [ ] 15.2 Capture visual regression baselines for MemberDashboard
  - Capture full dashboard at all viewports
  - Capture individual card variants
  - Capture loading states
  - Capture empty state

### Phase 13: Dark Mode Verification (Nice-to-Have)

- [ ] 16. Verify dark mode compatibility
  - Test all components with TailwindCSS dark: variants
  - Verify neo-brutalism borders and shadows are visible in dark mode
  - Verify StatCard colors adapt correctly
  - Verify chart colors work in dark mode
  - _Requirements: 15.1-15.5_

### Phase 14: Final Checkpoint

- [ ] 17. Final checkpoint - Complete verification
  - Ensure all unit tests pass
  - Ensure all property-based tests pass
  - Ensure all integration tests pass
  - Ensure visual regression tests pass
  - Verify accessibility audit has no critical issues
  - Verify all existing dashboard functionality is preserved
  - Run manual smoke test on both dashboards
  - Ask the user if questions arise.

---

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation at key milestones
- Property-based tests validate universal correctness properties defined in design document
- Unit tests validate specific examples and edge cases
- Visual regression tests catch unintended visual changes
- No backend changes required - all data flow from DashboardController is preserved

## Test Summary

| Test Type | Count | Purpose |
|-----------|-------|---------|
| Unit Tests | 12 | Verify specific component behaviors |
| Property Tests | 10 | Verify universal properties across generated inputs |
| Integration Tests | 2 | Verify data flow and navigation |
| Visual Regression Tests | 2 | Catch unintended visual changes |
| Accessibility Tests | 2 | Verify WCAG compliance |

## Component Summary

| Component | File | Purpose |
|-----------|------|---------|
| BentoGrid | `BentoGrid.vue` | CSS Grid container with responsive layout |
| BentoGridItem | `BentoGridItem.vue` | Grid item wrapper with span configuration |
| StatCard | `StatCard.vue` | Statistics display with neo-brutalism styling |
| DeadlineAlertCard | `DeadlineAlertCard.vue` | Overdue/due-soon task alerts |
| ChartCard | `ChartCard.vue` | ApexCharts wrapper with card styling |
| TeamPerformanceCard | `TeamPerformanceCard.vue` | Team performance table |
| TaskListCard | `TaskListCard.vue` | Task list table with two variants |
| ErrorState | `ErrorState.vue` | Error display with retry functionality |
