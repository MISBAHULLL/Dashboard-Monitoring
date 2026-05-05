# Requirements Document

## Introduction

This document defines the requirements for redesigning the existing admin and member dashboards using a Bento Grid layout pattern with neo-brutalism design touches. The redesign aims to modernize the visual appearance while preserving all existing functionality, data visualization, and user workflows. The Bento Grid pattern will create an asymmetric, visually engaging layout with varied card sizes, while neo-brutalism elements will add bold borders, vibrant colors, and a raw aesthetic to specific components.

## Glossary

- **Bento_Grid**: A grid-based layout pattern inspired by Japanese bento boxes, featuring asymmetric card sizes and positions that create visual hierarchy and interest
- **Neo_Brutalism_Style**: A design aesthetic characterized by bold borders, vibrant colors, raw/unpolished elements, strong shadows, and high-contrast visual treatments
- **Dashboard_Component**: An individual card or section within the dashboard that displays specific data or functionality
- **Stat_Card**: A dashboard component displaying numerical statistics with iconography
- **Chart_Component**: A dashboard component containing data visualizations (donut charts, area charts)
- **Task_List_Component**: A dashboard component displaying task information in tabular or list format
- **Admin_Dashboard**: The dashboard view visible to users with admin role, containing comprehensive system-wide metrics
- **Member_Dashboard**: The dashboard view visible to regular members, showing personal task assignments
- **ApexCharts**: The JavaScript charting library currently used for data visualization
- **TailwindCSS**: The utility-first CSS framework used for styling
- **Vue_3**: The JavaScript framework used for the frontend components
- **Inertia**: The library used for building single-page applications with classic server-side routing

## Requirements

### Requirement 1: Bento Grid Layout System

**User Story:** As a user, I want the dashboard to use a Bento Grid layout, so that information is presented in a visually engaging and organized manner with clear visual hierarchy.

#### Acceptance Criteria

1. THE Dashboard_Component SHALL be arranged in an asymmetric grid layout with varied card sizes (small, medium, large spanning multiple columns/rows)
2. THE Bento_Grid SHALL use CSS Grid with defined column and row spans to create the asymmetric layout pattern
3. THE Dashboard_Component SHALL maintain consistent spacing and alignment within the grid structure
4. THE Bento_Grid SHALL be responsive and adapt to different screen sizes while maintaining visual hierarchy
5. WHEN the viewport width is below 768px, THE Bento_Grid SHALL collapse to a single-column layout while preserving content order

### Requirement 2: Subtle Neo-Brutalism Design System

**User Story:** As a user, I want subtle neo-brutalism design elements applied to the dashboard, so that the interface has a modern and distinctive visual identity while maintaining a professional and trustworthy appearance suitable for a healthtech B2B product.

#### Acceptance Criteria

1. THE Stat_Card SHALL display with slightly bolder borders (1.5px solid border) in high-contrast colors
2. THE Stat_Card SHALL use mild shadow effects (subtle offset shadows) as a subtle neo-brutalism design element
3. THE Dashboard_Component SHALL use the Trustmedis brand color palette (navy, green, red, amber) defined in the design system
4. THE Stat_Card SHALL display icons with professional stroke weights and refined background shapes
5. THE Dashboard_Component SHALL use sans-serif typography with appropriate weights for headings and statistics
6. WHERE subtle neo-brutalism styling is applied, THE Dashboard_Component SHALL maintain readable contrast ratios (minimum 4.5:1 for text)
7. THE design SHALL prioritize a professional and trustworthy aesthetic over raw/edgy visual elements

### Requirement 3: Admin Dashboard Statistics Cards

**User Story:** As an admin, I want statistics cards displayed prominently in the Bento Grid, so that I can quickly understand key system metrics at a glance.

#### Acceptance Criteria

1. THE Admin_Dashboard SHALL display total tasks, open tasks, total clients, and total teams statistics in Stat_Card components
2. THE total tasks Stat_Card SHALL span a prominent position in the Bento_Grid layout
3. THE open tasks Stat_Card SHALL display with navy pale color treatment (#E8EEF8) in the subtle neo-brutalism style
4. THE total clients Stat_Card SHALL display with Trustmedis green color treatment (#2BAE6E) in the subtle neo-brutalism style
5. THE total teams Stat_Card SHALL display with Trustmedis navy color treatment (#1B3A6B) in the subtle neo-brutalism style
6. WHEN hovered, THE Stat_Card SHALL provide visual feedback through shadow shift or border color change

### Requirement 4: Admin Dashboard Deadline Alerts Section

**User Story:** As an admin, I want overdue and due-soon tasks displayed in a dedicated section, so that I can identify and address urgent task items quickly.

#### Acceptance Criteria

1. THE Admin_Dashboard SHALL display overdue tasks in a distinct Dashboard_Component with Trustmedis red color treatment (#E84545)
2. THE Admin_Dashboard SHALL display due-soon tasks (within 7 days) in a distinct Dashboard_Component with Trustmedis amber color treatment (#F59E0B)
3. THE overdue Dashboard_Component SHALL display up to 10 overdue tasks with title, client name, and release date
4. THE due-soon Dashboard_Component SHALL display up to 10 due-soon tasks with title, client name, and release date
5. WHEN no overdue or due-soon tasks exist, THE Dashboard_Component SHALL display an appropriate empty state message
6. THE deadline alert Dashboard_Component SHALL be positioned prominently in the Bento_Grid layout
7. WHEN overdue tasks exceed 10 items, a "Lihat Semua" link SHALL be displayed linking to the full task list filtered by overdue status
8. WHEN due-soon tasks exceed 10 items, a "Lihat Semua" link SHALL be displayed linking to the full task list filtered by due-soon status

### Requirement 5: Admin Dashboard Chart Visualizations

**User Story:** As an admin, I want charts displayed in the Bento Grid layout, so that I can visualize task trends and status distributions effectively.

#### Acceptance Criteria

1. THE Admin_Dashboard SHALL display the area chart showing task creation trends for the last 7 days
2. THE Admin_Dashboard SHALL display the donut chart showing task status distribution (open, in progress, revision, completed)
3. THE area chart Dashboard_Component SHALL span multiple columns in the Bento_Grid to provide adequate visualization space
4. THE donut chart Dashboard_Component SHALL occupy a single column in the Bento_Grid layout
5. THE Chart_Component SHALL maintain existing ApexCharts configuration and functionality
6. THE Chart_Component container SHALL use subtle neo-brutalism styling for borders and shadows while keeping the chart visualization readable

### Requirement 6: Admin Dashboard Team Performance Section

**User Story:** As an admin, I want team performance data displayed in the Bento Grid, so that I can monitor team productivity and identify areas needing attention.

#### Acceptance Criteria

1. THE Admin_Dashboard SHALL display a team performance table showing team name, total tasks, completed tasks, overdue tasks, and completion rate
2. THE team performance Dashboard_Component SHALL span the full width of the Bento_Grid layout
3. THE completion rate SHALL be displayed as a percentage with visual indicator (badge or progress bar)
4. WHEN team data is empty, THE Dashboard_Component SHALL display an appropriate empty state message
5. THE team performance Dashboard_Component SHALL display up to 10 teams ordered by total tasks descending

### Requirement 7: Admin Dashboard Recent Tasks Section

**User Story:** As an admin, I want recent tasks displayed in the Bento Grid, so that I can see the latest activity in the system.

#### Acceptance Criteria

1. THE Admin_Dashboard SHALL display a recent tasks table showing the 5 most recently created tasks
2. THE recent tasks Dashboard_Component SHALL display client name, task title, module, status, and creation date
3. THE status column SHALL display color-coded badges matching the existing status color scheme
4. THE recent tasks Dashboard_Component SHALL span appropriate columns in the Bento_Grid layout
5. WHEN no recent tasks exist, THE Dashboard_Component SHALL display an appropriate empty state message

### Requirement 8: Member Dashboard Statistics Cards

**User Story:** As a member, I want my task statistics displayed prominently in the Bento Grid, so that I can understand my workload at a glance.

#### Acceptance Criteria

1. THE Member_Dashboard SHALL display open tasks, in-progress tasks, and completed tasks statistics in Stat_Card components
2. THE open tasks Stat_Card SHALL display with Trustmedis amber color treatment (#F59E0B) in the subtle neo-brutalism style
3. THE in-progress tasks Stat_Card SHALL display with Trustmedis navy color treatment (#1B3A6B) in the subtle neo-brutalism style
4. THE completed tasks Stat_Card SHALL display with Trustmedis green color treatment (#2BAE6E) in the subtle neo-brutalism style
5. THE Stat_Card SHALL be arranged in the Bento_Grid layout with varied card sizes creating visual interest

### Requirement 9: Member Dashboard Task List

**User Story:** As a member, I want my assigned tasks displayed in the Bento Grid, so that I can manage my work effectively.

#### Acceptance Criteria

1. THE Member_Dashboard SHALL display a task list showing assigned tasks that are not completed
2. THE task list Dashboard_Component SHALL display client name, task title, priority, and status
3. THE priority column SHALL display color-coded badges (urgent=red, high=amber, medium=blue, low=slate)
4. THE status column SHALL display color-coded badges matching the existing status color scheme
5. THE task list Dashboard_Component SHALL display up to 5 tasks
6. WHEN no pending tasks exist, THE Dashboard_Component SHALL display a celebratory empty state message

### Requirement 10: Responsive Design Adaptation

**User Story:** As a user, I want the dashboard to work well on all device sizes, so that I can access information from desktop, tablet, or mobile devices.

#### Acceptance Criteria

1. WHEN the viewport width is 1024px or greater, THE Bento_Grid SHALL display in a multi-column asymmetric layout
2. WHEN the viewport width is between 768px and 1023px, THE Bento_Grid SHALL adapt to a reduced column layout
3. WHEN the viewport width is below 768px, THE Bento_Grid SHALL collapse to a single-column stacked layout
4. THE Dashboard_Component SHALL maintain readable font sizes across all viewport widths
5. THE Chart_Component SHALL resize appropriately to fit within the Bento_Grid at all viewport sizes

### Requirement 11: Data Integrity Preservation

**User Story:** As a user, I want all existing dashboard data and functionality to remain intact, so that I do not lose any capabilities during the redesign.

#### Acceptance Criteria

1. THE Admin_Dashboard SHALL receive and display all data properties from the DashboardController without modification
2. THE Member_Dashboard SHALL receive and display all data properties from the DashboardController without modification
3. THE DashboardController SHALL not require modifications to support the Bento_Grid layout redesign
4. THE Chart_Component SHALL maintain all existing ApexCharts options and configurations
5. THE navigation breadcrumbs SHALL remain functional and unchanged

### Requirement 12: Accessibility Compliance

**User Story:** As a user with accessibility needs, I want the dashboard to be accessible, so that I can use it effectively with assistive technologies.

#### Acceptance Criteria

1. THE Dashboard_Component SHALL use semantic HTML elements (section, article, header) for structure
2. THE Stat_Card SHALL have appropriate ARIA labels describing the statistic being displayed
3. THE color combinations used in neo-brutalism styling SHALL maintain minimum contrast ratios of 4.5:1 for normal text
4. THE Chart_Component SHALL provide text alternatives for data visualization (accessible data tables or descriptions)
5. WHEN using keyboard navigation, THE Dashboard_Component SHALL be focusable in a logical reading order

### Requirement 13: Component Reusability

**User Story:** As a developer, I want reusable Bento Grid components, so that the dashboard can be maintained and extended efficiently.

#### Acceptance Criteria

1. THE Dashboard_Component SHALL be implemented as a reusable Vue component accepting props for content and styling variants
2. THE Stat_Card component SHALL accept props for statistic value, label, icon, and color theme
3. THE Bento_Grid container SHALL accept props for responsive breakpoints and column configuration
4. THE neo-brutalism styles SHALL be implemented as TailwindCSS utility classes that can be applied consistently
5. THE Dashboard_Component SHALL support slot-based content insertion for flexibility

### Requirement 14: Visual Hierarchy and Focus

**User Story:** As a user, I want visual hierarchy in the dashboard, so that I can quickly identify the most important information.

#### Acceptance Criteria

1. THE overdue tasks Dashboard_Component SHALL be sized and positioned prominently in the Bento_Grid to draw attention
2. THE Stat_Card displaying critical metrics (overdue count, open tasks) SHALL use larger card sizes
3. THE neo-brutalism color intensity SHALL be used to indicate urgency (red for critical, amber for warning, blue for neutral, green for positive)
4. THE Dashboard_Component with charts SHALL use larger card sizes to ensure data visualization readability
5. THE visual hierarchy SHALL guide the user's eye from most critical (overdue) to least critical (recent activity) information

### Requirement 15: Dark Mode Compatibility (Nice-to-Have)

**Priority:** Low (Not MVP)

**User Story:** As a user, I want the dashboard to support dark mode, so that I can use it comfortably in low-light environments.

#### Acceptance Criteria

1. WHEN dark mode is enabled, THE Dashboard_Component SHALL display with appropriate dark background colors
2. WHEN dark mode is enabled, THE subtle neo-brutalism borders and shadows SHALL use colors that maintain contrast and visibility
3. WHEN dark mode is enabled, THE Stat_Card colors SHALL adapt to maintain readability
4. THE Chart_Component SHALL display with appropriate colors in dark mode
5. THE existing TailwindCSS dark mode utilities (dark:) SHALL be used for dark mode styling

### Requirement 16: Loading States

**User Story:** As a user, I want to see loading indicators while dashboard data is being fetched, so that I understand the system is working and not frozen.

#### Acceptance Criteria

1. WHEN dashboard data is being fetched, THE Dashboard_Component SHALL display a skeleton loader matching the component's layout structure
2. THE skeleton loader SHALL use subtle animation (pulse or shimmer) to indicate loading state
3. THE skeleton loader SHALL maintain the same dimensions as the loaded content to prevent layout shift
4. EACH Stat_Card, Chart_Component, and Task_List_Component SHALL have its own skeleton loader variant
5. THE skeleton loader SHALL use neutral colors that work in both light and dark modes

### Requirement 17: Error States

**User Story:** As a user, I want to see clear error messages when data fails to load, so that I understand what went wrong and can take action to resolve it.

#### Acceptance Criteria

1. WHEN data fetch fails for any Dashboard_Component, THE component SHALL display an inline error message
2. THE error message SHALL clearly describe what failed to load (e.g., "Gagal memuat data task overdue")
3. THE error state SHALL include a "Coba Lagi" (Retry) button or action to refetch the data
4. THE error state SHALL use Trustmedis red color (#E84545) for error indicators
5. THE error state SHALL maintain the component's container dimensions to prevent layout shift
6. WHEN the retry action is triggered, THE component SHALL attempt to refetch the data and return to loading state
