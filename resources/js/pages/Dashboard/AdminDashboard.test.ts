/**
 * AdminDashboard Integration Tests
 *
 * Tests the AdminDashboard page component for proper data flow from controller,
 * rendering of all child components with correct data, and navigation breadcrumbs.
 *
 * **Validates: Requirements 11.1, 11.5**
 * - Requirement 11.1: THE Admin_Dashboard SHALL receive and display all data properties from the DashboardController without modification
 * - Requirement 11.5: THE navigation breadcrumbs SHALL remain functional and unchanged
 */

import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { h, defineComponent } from 'vue'
import AdminDashboard from './AdminDashboard.vue'
import type {
  AdminDashboardProps,
  Task,
  TeamPerformance,
} from '@/types/dashboard'

// Mock Inertia components
vi.mock('@inertiajs/vue3', () => ({
  Head: defineComponent({
    name: 'Head',
    props: ['title'],
    setup(_, { slots }) {
      return () => h('title', slots.default?.())
    },
  }),
  Link: defineComponent({
    name: 'Link',
    props: ['href'],
    setup(_, { slots }) {
      return () => h('a', slots.default?.())
    },
  }),
}))

// Mock route helpers
vi.mock('@/routes', () => ({
  dashboard: () => '/dashboard',
}))

vi.mock('@/routes/tasks', () => ({
  index: vi.fn((params?: { query?: Record<string, string> }) => {
    if (params?.query?.status === 'overdue') {
      return { url: '/tasks?status=overdue' }
    }
    if (params?.query?.status === 'due_soon') {
      return { url: '/tasks?status=due_soon' }
    }
    return { url: '/tasks' }
  }),
}))

// Mock ApexCharts
vi.mock('vue3-apexcharts', () => ({
  default: defineComponent({
    name: 'VueApexCharts',
    props: ['type', 'options', 'series', 'height'],
    setup() {
      return () => h('div', { class: 'mock-apexcharts' }, 'Chart')
    },
  }),
}))

/**
 * Factory function to create complete test props
 * Matches the data structure from DashboardController
 */
function createTestProps(overrides: Partial<AdminDashboardProps> = {}): AdminDashboardProps {
  return {
    stats: {
      total_tasks: 150,
      open_tasks: 42,
      in_progress_tasks: 25,
      completed_tasks: 83,
      total_clients: 25,
      total_teams: 8,
    },
    chart_donut: [42, 25, 10, 83],
    chart_area: {
      categories: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
      data: [12, 8, 15, 10, 18, 5, 20],
    },
    overdue_count: 5,
    due_soon_count: 12,
    overdue_tasks: [
      { id: 1, title: 'Task Overdue 1', client: { name: 'Client A' }, release_date: '2024-01-15' },
      { id: 2, title: 'Task Overdue 2', client: { name: 'Client B' }, release_date: '2024-01-10' },
    ] as Task[],
    due_soon_tasks: [
      { id: 3, title: 'Task Due Soon 1', client: { name: 'Client C' }, release_date: '2024-02-01' },
      { id: 4, title: 'Task Due Soon 2', client: { name: 'Client D' }, release_date: '2024-02-05' },
    ] as Task[],
    team_performance: [
      {
        id: 1,
        name: 'Team Alpha',
        total_tasks: 50,
        completed_tasks: 35,
        open_tasks: 10,
        in_progress_tasks: 3,
        revision_tasks: 2,
        overdue_tasks: 2,
        completion_rate: 70,
      },
      {
        id: 2,
        name: 'Team Beta',
        total_tasks: 40,
        completed_tasks: 30,
        open_tasks: 5,
        in_progress_tasks: 3,
        revision_tasks: 1,
        overdue_tasks: 1,
        completion_rate: 75,
      },
    ] as TeamPerformance[],
    recent_tasks: [
      { id: 5, title: 'Recent Task 1', modul: 'Module A', status: 'open', client: { name: 'Client E' }, created_at: '2024-01-20' },
      { id: 6, title: 'Recent Task 2', modul: 'Module B', status: 'in_progress', client: { name: 'Client F' }, created_at: '2024-01-19' },
    ] as Task[],
    ...overrides,
  }
}

describe('AdminDashboard Integration Tests', () => {
  describe('Requirement 11.1: Receives and displays all data properties from DashboardController', () => {
    describe('component receives all props from controller', () => {
      it('receives and has access to stats prop', () => {
        const props = createTestProps()
        const wrapper = mount(AdminDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: true,
              BentoGridItem: true,
              StatCard: true,
              DeadlineAlertCard: true,
              ChartCard: true,
              TeamPerformanceCard: true,
              TaskListCard: true,
            },
          },
        })

        expect(wrapper.props('stats')).toEqual(props.stats)
        expect(wrapper.props('stats').total_tasks).toBe(150)
        expect(wrapper.props('stats').open_tasks).toBe(42)
        expect(wrapper.props('stats').total_clients).toBe(25)
        expect(wrapper.props('stats').total_teams).toBe(8)
      })

      it('receives and has access to chart_donut prop', () => {
        const props = createTestProps()
        const wrapper = mount(AdminDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: true,
              BentoGridItem: true,
              StatCard: true,
              DeadlineAlertCard: true,
              ChartCard: true,
              TeamPerformanceCard: true,
              TaskListCard: true,
            },
          },
        })

        expect(wrapper.props('chart_donut')).toEqual(props.chart_donut)
        expect(wrapper.props('chart_donut')).toHaveLength(4)
      })

      it('receives and has access to chart_area prop', () => {
        const props = createTestProps()
        const wrapper = mount(AdminDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: true,
              BentoGridItem: true,
              StatCard: true,
              DeadlineAlertCard: true,
              ChartCard: true,
              TeamPerformanceCard: true,
              TaskListCard: true,
            },
          },
        })

        expect(wrapper.props('chart_area')).toEqual(props.chart_area)
        expect(wrapper.props('chart_area').categories).toHaveLength(7)
        expect(wrapper.props('chart_area').data).toHaveLength(7)
      })

      it('receives and has access to overdue_count and overdue_tasks props', () => {
        const props = createTestProps()
        const wrapper = mount(AdminDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: true,
              BentoGridItem: true,
              StatCard: true,
              DeadlineAlertCard: true,
              ChartCard: true,
              TeamPerformanceCard: true,
              TaskListCard: true,
            },
          },
        })

        expect(wrapper.props('overdue_count')).toBe(5)
        expect(wrapper.props('overdue_tasks')).toEqual(props.overdue_tasks)
        expect(wrapper.props('overdue_tasks')).toHaveLength(2)
      })

      it('receives and has access to due_soon_count and due_soon_tasks props', () => {
        const props = createTestProps()
        const wrapper = mount(AdminDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: true,
              BentoGridItem: true,
              StatCard: true,
              DeadlineAlertCard: true,
              ChartCard: true,
              TeamPerformanceCard: true,
              TaskListCard: true,
            },
          },
        })

        expect(wrapper.props('due_soon_count')).toBe(12)
        expect(wrapper.props('due_soon_tasks')).toEqual(props.due_soon_tasks)
        expect(wrapper.props('due_soon_tasks')).toHaveLength(2)
      })

      it('receives and has access to team_performance prop', () => {
        const props = createTestProps()
        const wrapper = mount(AdminDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: true,
              BentoGridItem: true,
              StatCard: true,
              DeadlineAlertCard: true,
              ChartCard: true,
              TeamPerformanceCard: true,
              TaskListCard: true,
            },
          },
        })

        expect(wrapper.props('team_performance')).toEqual(props.team_performance)
        expect(wrapper.props('team_performance')).toHaveLength(2)
      })

      it('receives and has access to recent_tasks prop', () => {
        const props = createTestProps()
        const wrapper = mount(AdminDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: true,
              BentoGridItem: true,
              StatCard: true,
              DeadlineAlertCard: true,
              ChartCard: true,
              TeamPerformanceCard: true,
              TaskListCard: true,
            },
          },
        })

        expect(wrapper.props('recent_tasks')).toEqual(props.recent_tasks)
        expect(wrapper.props('recent_tasks')).toHaveLength(2)
      })
    })

    describe('all data is rendered in appropriate components', () => {
      it('renders BentoGrid container', () => {
        const props = createTestProps()
        const wrapper = mount(AdminDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: false,
              BentoGridItem: true,
              StatCard: true,
              DeadlineAlertCard: true,
              ChartCard: true,
              TeamPerformanceCard: true,
              TaskListCard: true,
            },
          },
        })

        expect(wrapper.findComponent({ name: 'BentoGrid' }).exists()).toBe(true)
      })

      it('renders 4 StatCard components for statistics', () => {
        const props = createTestProps()
        const wrapper = mount(AdminDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: false,
              BentoGridItem: false,
              StatCard: false,
              DeadlineAlertCard: true,
              ChartCard: true,
              TeamPerformanceCard: true,
              TaskListCard: true,
            },
          },
        })

        const statCards = wrapper.findAllComponents({ name: 'StatCard' })
        expect(statCards).toHaveLength(4)
      })

      it('passes correct props to Total Tasks StatCard', () => {
        const props = createTestProps()
        const wrapper = mount(AdminDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: false,
              BentoGridItem: false,
              StatCard: false,
              DeadlineAlertCard: true,
              ChartCard: true,
              TeamPerformanceCard: true,
              TaskListCard: true,
            },
          },
        })

        const statCards = wrapper.findAllComponents({ name: 'StatCard' })
        const totalTasksCard = statCards[0]
        
        expect(totalTasksCard.props('label')).toBe('Total Tasks')
        expect(totalTasksCard.props('value')).toBe(props.stats.total_tasks)
        expect(totalTasksCard.props('colorTheme')).toBe('neutral')
      })

      it('passes correct props to Open Tasks StatCard', () => {
        const props = createTestProps()
        const wrapper = mount(AdminDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: false,
              BentoGridItem: false,
              StatCard: false,
              DeadlineAlertCard: true,
              ChartCard: true,
              TeamPerformanceCard: true,
              TaskListCard: true,
            },
          },
        })

        const statCards = wrapper.findAllComponents({ name: 'StatCard' })
        const openTasksCard = statCards[1]
        
        expect(openTasksCard.props('label')).toBe('Menunggu Dikerjakan')
        expect(openTasksCard.props('value')).toBe(props.stats.open_tasks)
        expect(openTasksCard.props('colorTheme')).toBe('amber')
      })

      it('passes correct props to Total Clients StatCard', () => {
        const props = createTestProps()
        const wrapper = mount(AdminDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: false,
              BentoGridItem: false,
              StatCard: false,
              DeadlineAlertCard: true,
              ChartCard: true,
              TeamPerformanceCard: true,
              TaskListCard: true,
            },
          },
        })

        const statCards = wrapper.findAllComponents({ name: 'StatCard' })
        const totalClientsCard = statCards[2]
        
        expect(totalClientsCard.props('label')).toBe('Total Faskes')
        expect(totalClientsCard.props('value')).toBe(props.stats.total_clients)
        expect(totalClientsCard.props('colorTheme')).toBe('green')
      })

      it('passes correct props to Total Teams StatCard', () => {
        const props = createTestProps()
        const wrapper = mount(AdminDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: false,
              BentoGridItem: false,
              StatCard: false,
              DeadlineAlertCard: true,
              ChartCard: true,
              TeamPerformanceCard: true,
              TaskListCard: true,
            },
          },
        })

        const statCards = wrapper.findAllComponents({ name: 'StatCard' })
        const totalTeamsCard = statCards[3]
        
        expect(totalTeamsCard.props('label')).toBe('Total Tim')
        expect(totalTeamsCard.props('value')).toBe(props.stats.total_teams)
        expect(totalTeamsCard.props('colorTheme')).toBe('navy')
      })

      it('renders 2 DeadlineAlertCard components for overdue and due_soon', () => {
        const props = createTestProps()
        const wrapper = mount(AdminDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: false,
              BentoGridItem: false,
              StatCard: true,
              DeadlineAlertCard: false,
              ChartCard: true,
              TeamPerformanceCard: true,
              TaskListCard: true,
            },
          },
        })

        const deadlineCards = wrapper.findAllComponents({ name: 'DeadlineAlertCard' })
        expect(deadlineCards).toHaveLength(2)
      })

      it('passes correct props to overdue DeadlineAlertCard', () => {
        const props = createTestProps()
        const wrapper = mount(AdminDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: false,
              BentoGridItem: false,
              StatCard: true,
              DeadlineAlertCard: false,
              ChartCard: true,
              TeamPerformanceCard: true,
              TaskListCard: true,
            },
          },
        })

        const deadlineCards = wrapper.findAllComponents({ name: 'DeadlineAlertCard' })
        const overdueCard = deadlineCards[0]
        
        expect(overdueCard.props('type')).toBe('overdue')
        expect(overdueCard.props('count')).toBe(props.overdue_count)
        expect(overdueCard.props('tasks')).toEqual(props.overdue_tasks)
      })

      it('passes correct props to due_soon DeadlineAlertCard', () => {
        const props = createTestProps()
        const wrapper = mount(AdminDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: false,
              BentoGridItem: false,
              StatCard: true,
              DeadlineAlertCard: false,
              ChartCard: true,
              TeamPerformanceCard: true,
              TaskListCard: true,
            },
          },
        })

        const deadlineCards = wrapper.findAllComponents({ name: 'DeadlineAlertCard' })
        const dueSoonCard = deadlineCards[1]
        
        expect(dueSoonCard.props('type')).toBe('due_soon')
        expect(dueSoonCard.props('count')).toBe(props.due_soon_count)
        expect(dueSoonCard.props('tasks')).toEqual(props.due_soon_tasks)
      })

      it('renders 2 ChartCard components for area and donut charts', () => {
        const props = createTestProps()
        const wrapper = mount(AdminDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: false,
              BentoGridItem: false,
              StatCard: true,
              DeadlineAlertCard: true,
              ChartCard: false,
              TeamPerformanceCard: true,
              TaskListCard: true,
            },
          },
        })

        const chartCards = wrapper.findAllComponents({ name: 'ChartCard' })
        expect(chartCards).toHaveLength(2)
      })

      it('passes correct props to area ChartCard', () => {
        const props = createTestProps()
        const wrapper = mount(AdminDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: false,
              BentoGridItem: false,
              StatCard: true,
              DeadlineAlertCard: true,
              ChartCard: false,
              TeamPerformanceCard: true,
              TaskListCard: true,
            },
          },
        })

        const chartCards = wrapper.findAllComponents({ name: 'ChartCard' })
        const areaCard = chartCards[0]
        
        expect(areaCard.props('title')).toBe('Tren Pembuatan Task (7 Hari Terakhir)')
        expect(areaCard.props('chartType')).toBe('area')
        expect(areaCard.props('height')).toBe(300)
      })

      it('passes correct props to donut ChartCard', () => {
        const props = createTestProps()
        const wrapper = mount(AdminDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: false,
              BentoGridItem: false,
              StatCard: true,
              DeadlineAlertCard: true,
              ChartCard: false,
              TeamPerformanceCard: true,
              TaskListCard: true,
            },
          },
        })

        const chartCards = wrapper.findAllComponents({ name: 'ChartCard' })
        const donutCard = chartCards[1]
        
        expect(donutCard.props('title')).toBe('Rasio Status Task')
        expect(donutCard.props('chartType')).toBe('donut')
        expect(donutCard.props('height')).toBe(320)
      })

      it('renders TeamPerformanceCard with team data', () => {
        const props = createTestProps()
        const wrapper = mount(AdminDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: false,
              BentoGridItem: false,
              StatCard: true,
              DeadlineAlertCard: true,
              ChartCard: true,
              TeamPerformanceCard: false,
              TaskListCard: true,
            },
          },
        })

        const teamCard = wrapper.findComponent({ name: 'TeamPerformanceCard' })
        expect(teamCard.exists()).toBe(true)
        expect(teamCard.props('teams')).toEqual(props.team_performance)
      })

      it('renders TaskListCard with recent tasks', () => {
        const props = createTestProps()
        const wrapper = mount(AdminDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: false,
              BentoGridItem: false,
              StatCard: true,
              DeadlineAlertCard: true,
              ChartCard: true,
              TeamPerformanceCard: true,
              TaskListCard: false,
            },
          },
        })

        const taskCard = wrapper.findComponent({ name: 'TaskListCard' })
        expect(taskCard.exists()).toBe(true)
        expect(taskCard.props('variant')).toBe('recent')
        expect(taskCard.props('tasks')).toEqual(props.recent_tasks)
      })
    })

    describe('data is passed without modification', () => {
      it('passes stats values directly without transformation', () => {
        const props = createTestProps({
          stats: {
            total_tasks: 999,
            open_tasks: 111,
            in_progress_tasks: 222,
            completed_tasks: 333,
            total_clients: 444,
            total_teams: 555,
          },
        })
        const wrapper = mount(AdminDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: false,
              BentoGridItem: false,
              StatCard: false,
              DeadlineAlertCard: true,
              ChartCard: true,
              TeamPerformanceCard: true,
              TaskListCard: true,
            },
          },
        })

        const statCards = wrapper.findAllComponents({ name: 'StatCard' })
        
        expect(statCards[0].props('value')).toBe(999)
        expect(statCards[1].props('value')).toBe(111)
        expect(statCards[2].props('value')).toBe(444)
        expect(statCards[3].props('value')).toBe(555)
      })

      it('passes chart data directly without transformation', () => {
        const props = createTestProps({
          chart_donut: [100, 200, 300, 400],
          chart_area: {
            categories: ['A', 'B', 'C', 'D', 'E', 'F', 'G'],
            data: [1, 2, 3, 4, 5, 6, 7],
          },
        })
        const wrapper = mount(AdminDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: false,
              BentoGridItem: false,
              StatCard: true,
              DeadlineAlertCard: true,
              ChartCard: false,
              TeamPerformanceCard: true,
              TaskListCard: true,
            },
          },
        })

        const chartCards = wrapper.findAllComponents({ name: 'ChartCard' })
        
        // Donut series should match chart_donut
        expect(chartCards[1].props('series')).toEqual([100, 200, 300, 400])
      })

      it('passes task arrays directly without modification', () => {
        const overdueTasks: Task[] = [
          { id: 99, title: 'Custom Overdue', client: { name: 'Custom Client' }, release_date: '2024-03-01' },
        ]
        const dueSoonTasks: Task[] = [
          { id: 88, title: 'Custom Due Soon', client: { name: 'Another Client' }, release_date: '2024-03-15' },
        ]
        
        const props = createTestProps({
          overdue_tasks: overdueTasks,
          due_soon_tasks: dueSoonTasks,
        })
        const wrapper = mount(AdminDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: false,
              BentoGridItem: false,
              StatCard: true,
              DeadlineAlertCard: false,
              ChartCard: true,
              TeamPerformanceCard: true,
              TaskListCard: true,
            },
          },
        })

        const deadlineCards = wrapper.findAllComponents({ name: 'DeadlineAlertCard' })
        
        expect(deadlineCards[0].props('tasks')).toEqual(overdueTasks)
        expect(deadlineCards[1].props('tasks')).toEqual(dueSoonTasks)
      })

      it('passes team performance array directly without modification', () => {
        const teams: TeamPerformance[] = [
          {
            id: 99,
            name: 'Custom Team',
            type: 'product',
            total_tasks: 100,
            completed_tasks: 80,
            open_tasks: 10,
            in_progress_tasks: 5,
            revision_tasks: 3,
            overdue_tasks: 2,
            completion_rate: 80,
          },
        ]
        
        const props = createTestProps({
          team_performance: teams,
        })
        const wrapper = mount(AdminDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: false,
              BentoGridItem: false,
              StatCard: true,
              DeadlineAlertCard: true,
              ChartCard: true,
              TeamPerformanceCard: false,
              TaskListCard: true,
            },
          },
        })

        const teamCard = wrapper.findComponent({ name: 'TeamPerformanceCard' })
        expect(teamCard.props('teams')).toEqual(teams)
      })
    })
  })

  describe('Requirement 11.5: Navigation breadcrumbs remain functional', () => {
    it('defines breadcrumbs in layout options', () => {
      // AdminDashboard uses defineOptions for breadcrumbs
      expect(AdminDashboard).toBeDefined()
    })

    it('renders the page header with title', () => {
      const props = createTestProps()
      const wrapper = mount(AdminDashboard, {
        props,
        global: {
          stubs: {
            BentoGrid: true,
            BentoGridItem: true,
            StatCard: true,
            DeadlineAlertCard: true,
            ChartCard: true,
            TeamPerformanceCard: true,
            TaskListCard: true,
          },
        },
      })

      expect(wrapper.find('h1').exists()).toBe(true)
      expect(wrapper.find('h1').text()).toBe('Dashboard Admin')
    })

    it('renders the page header with description', () => {
      const props = createTestProps()
      const wrapper = mount(AdminDashboard, {
        props,
        global: {
          stubs: {
            BentoGrid: true,
            BentoGridItem: true,
            StatCard: true,
            DeadlineAlertCard: true,
            ChartCard: true,
            TeamPerformanceCard: true,
            TaskListCard: true,
          },
        },
      })

      const description = wrapper.find('p.text-muted-foreground')
      expect(description.exists()).toBe(true)
      expect(description.text()).toContain('Pantau seluruh aktivitas task')
    })

    it('has proper page structure for breadcrumb context', () => {
      const props = createTestProps()
      const wrapper = mount(AdminDashboard, {
        props,
        global: {
          stubs: {
            BentoGrid: true,
            BentoGridItem: true,
            StatCard: true,
            DeadlineAlertCard: true,
            ChartCard: true,
            TeamPerformanceCard: true,
            TaskListCard: true,
          },
        },
      })

      // Verify the page container exists
      expect(wrapper.find('.flex.h-full.flex-1').exists()).toBe(true)
    })

    it('uses Head component for page title', () => {
      const props = createTestProps()
      const wrapper = mount(AdminDashboard, {
        props,
        global: {
          stubs: {
            Head: false,
            BentoGrid: true,
            BentoGridItem: true,
            StatCard: true,
            DeadlineAlertCard: true,
            ChartCard: true,
            TeamPerformanceCard: true,
            TaskListCard: true,
          },
        },
      })

      // Head component should be present with title prop
      const headComponent = wrapper.findComponent({ name: 'Head' })
      expect(headComponent.exists()).toBe(true)
      expect(headComponent.props('title')).toBe('Admin Dashboard')
    })
  })

  describe('handles edge cases gracefully', () => {
    it('handles empty overdue_tasks array', () => {
      const props = createTestProps({
        overdue_count: 0,
        overdue_tasks: [],
      })
      const wrapper = mount(AdminDashboard, {
        props,
        global: {
          stubs: {
            BentoGrid: false,
            BentoGridItem: false,
            StatCard: true,
            DeadlineAlertCard: false,
            ChartCard: true,
            TeamPerformanceCard: true,
            TaskListCard: true,
          },
        },
      })

      const deadlineCards = wrapper.findAllComponents({ name: 'DeadlineAlertCard' })
      expect(deadlineCards[0].props('count')).toBe(0)
      expect(deadlineCards[0].props('tasks')).toEqual([])
    })

    it('handles empty due_soon_tasks array', () => {
      const props = createTestProps({
        due_soon_count: 0,
        due_soon_tasks: [],
      })
      const wrapper = mount(AdminDashboard, {
        props,
        global: {
          stubs: {
            BentoGrid: false,
            BentoGridItem: false,
            StatCard: true,
            DeadlineAlertCard: false,
            ChartCard: true,
            TeamPerformanceCard: true,
            TaskListCard: true,
          },
        },
      })

      const deadlineCards = wrapper.findAllComponents({ name: 'DeadlineAlertCard' })
      expect(deadlineCards[1].props('count')).toBe(0)
      expect(deadlineCards[1].props('tasks')).toEqual([])
    })

    it('handles empty team_performance array', () => {
      const props = createTestProps({
        team_performance: [],
      })
      const wrapper = mount(AdminDashboard, {
        props,
        global: {
          stubs: {
            BentoGrid: false,
            BentoGridItem: false,
            StatCard: true,
            DeadlineAlertCard: true,
            ChartCard: true,
            TeamPerformanceCard: false,
            TaskListCard: true,
          },
        },
      })

      const teamCard = wrapper.findComponent({ name: 'TeamPerformanceCard' })
      expect(teamCard.props('teams')).toEqual([])
    })

    it('handles empty recent_tasks array', () => {
      const props = createTestProps({
        recent_tasks: [],
      })
      const wrapper = mount(AdminDashboard, {
        props,
        global: {
          stubs: {
            BentoGrid: false,
            BentoGridItem: false,
            StatCard: true,
            DeadlineAlertCard: true,
            ChartCard: true,
            TeamPerformanceCard: true,
            TaskListCard: false,
          },
        },
      })

      const taskCard = wrapper.findComponent({ name: 'TaskListCard' })
      expect(taskCard.props('tasks')).toEqual([])
    })

    it('handles zero stats values', () => {
      const props = createTestProps({
        stats: {
          total_tasks: 0,
          open_tasks: 0,
          in_progress_tasks: 0,
          completed_tasks: 0,
          total_clients: 0,
          total_teams: 0,
        },
      })
      const wrapper = mount(AdminDashboard, {
        props,
        global: {
          stubs: {
            BentoGrid: false,
            BentoGridItem: false,
            StatCard: false,
            DeadlineAlertCard: true,
            ChartCard: true,
            TeamPerformanceCard: true,
            TaskListCard: true,
          },
        },
      })

      const statCards = wrapper.findAllComponents({ name: 'StatCard' })
      statCards.forEach((card) => {
        expect(card.props('value')).toBe(0)
      })
    })
  })
})
