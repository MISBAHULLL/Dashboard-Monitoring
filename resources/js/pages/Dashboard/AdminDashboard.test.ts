import { mount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'
import { defineComponent, h } from 'vue'
import type { AdminDashboardProps } from '@/types/dashboard'
import AdminDashboard from './AdminDashboard.vue'

vi.mock('@inertiajs/vue3', () => ({
  Head: defineComponent({
    name: 'InertiaHead',
    props: ['title'],
    setup(props) {
      return () => h('title', props.title as string)
    },
  }),
  Link: defineComponent({
    name: 'InertiaLink',
    props: ['href'],
    setup(props, { slots }) {
      return () => h('a', { href: props.href }, slots.default?.())
    },
  }),
  usePage: () => ({
    props: {
      auth: {
        user: {
          name: 'Admin User',
        },
      },
    },
  }),
}))

vi.mock('@/routes', () => ({
  dashboard: () => '/dashboard',
}))

vi.mock('@/routes/tasks', () => ({
  index: vi.fn((params?: { query?: Record<string, string> }) => ({
    url: params?.query
      ? `/tasks?${new URLSearchParams(params.query).toString()}`
      : '/tasks',
  })),
}))

const dashboardStubs = {
  HeroCard: true,
  ActionsCard: true,
  GridTaskCard: true,
  TaskOverdueCard: true,
  TaskDueSoonCard: true,
  TaskTrendCard: true,
  RasioStatusTaskCard: true,
  TeamPerformanceCard: true,
  TaskListCard: true,
}

function createTestProps(overrides: Partial<AdminDashboardProps> = {}): AdminDashboardProps {
  return {
    stats: {
      total_tasks: 150,
      active_tasks: 150,
      trashed_tasks: 5,
      total_tasks_with_trashed: 155,
      open_tasks: 42,
      in_progress_tasks: 25,
      completed_tasks: 83,
      total_clients: 25,
      total_teams: 8,
    },
    trends: {
      tasks: 7,
      teams: 2,
      pending: 3,
      clients: 4,
    },
    chart_donut: [42, 25, 10, 83],
    chart_area: {
      categories: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
      data: [12, 8, 15, 10, 18, 5, 20],
    },
    chart_month: {
      categories: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
      data: [30, 45, 35, 40],
    },
    overdue_count: 5,
    due_soon_count: 12,
    overdue_tasks: [
      { id: 1, title: 'Task Overdue 1', client: { name: 'Client A' }, release_date: '2024-01-15' },
      { id: 2, title: 'Task Overdue 2', client: { name: 'Client B' }, release_date: '2024-01-10' },
    ],
    due_soon_tasks: [
      { id: 3, title: 'Task Due Soon 1', client: { name: 'Client C' }, release_date: '2024-02-01' },
      { id: 4, title: 'Task Due Soon 2', client: { name: 'Client D' }, release_date: '2024-02-05' },
    ],
    team_performance: [
      {
        id: 1,
        name: 'Team Alpha',
        type: 'PRODUCT',
        total_tasks: 50,
        completed_tasks: 35,
        open_tasks: 10,
        in_progress_tasks: 3,
        revision_tasks: 2,
        overdue_tasks: 2,
        completion_rate: 70,
      },
    ],
    recent_tasks: [
      {
        id: 5,
        title: 'Recent Task 1',
        modul: 'Module A',
        status: 'open',
        client: { name: 'Client E' },
        created_at: '2024-01-20',
      },
    ],
    ...overrides,
  }
}

function mountDashboard(props: AdminDashboardProps = createTestProps()) {
  return mount(AdminDashboard, {
    props,
    global: {
      stubs: dashboardStubs,
    },
  })
}

describe('AdminDashboard', () => {
  it('receives the full controller payload', () => {
    const props = createTestProps()
    const wrapper = mountDashboard(props)

    expect(wrapper.props()).toMatchObject(props)
  })

  it('passes summary values into the hero and grid cards', () => {
    const props = createTestProps()
    const wrapper = mountDashboard(props)

    expect(wrapper.findComponent({ name: 'HeroCard' }).props()).toMatchObject({
      userName: 'Admin User',
      pendingCount: props.stats.open_tasks,
      overdueCount: props.overdue_count,
      totalTasks: props.stats.total_tasks,
    })
    expect(wrapper.findComponent({ name: 'GridTaskCard' }).props()).toMatchObject({
      totalTasks: props.stats.total_tasks,
      totalTeams: props.stats.total_teams,
      pendingTasks: props.stats.open_tasks,
      totalClients: props.stats.total_clients,
      trends: props.trends,
    })
  })

  it('passes deadline, chart, team, and recent task data without reshaping', () => {
    const props = createTestProps()
    const wrapper = mountDashboard(props)

    expect(wrapper.findComponent({ name: 'TaskDueSoonCard' }).props('tasks')).toEqual(props.due_soon_tasks)
    expect(wrapper.findComponent({ name: 'TaskOverdueCard' }).props('tasks')).toEqual(props.overdue_tasks)
    expect(wrapper.findComponent({ name: 'RasioStatusTaskCard' }).props('series')).toEqual(props.chart_donut)
    expect(wrapper.findComponent({ name: 'TeamPerformanceCard' }).props('teams')).toEqual(props.team_performance)
    expect(wrapper.findComponent({ name: 'TaskListCard' }).props('tasks')).toEqual(props.recent_tasks)
  })

  it('passes weekly and monthly trend data to TaskTrendCard', () => {
    const props = createTestProps()
    const wrapper = mountDashboard(props)
    const trendCard = wrapper.findComponent({ name: 'TaskTrendCard' })

    expect(trendCard.props()).toMatchObject({
      categories: props.chart_area.categories,
      data: props.chart_area.data,
      monthlyCategories: props.chart_month.categories,
      monthlyData: props.chart_month.data,
    })
  })

  it('handles empty dashboard collections', () => {
    const props = createTestProps({
      overdue_count: 0,
      due_soon_count: 0,
      overdue_tasks: [],
      due_soon_tasks: [],
      team_performance: [],
      recent_tasks: [],
    })
    const wrapper = mountDashboard(props)

    expect(wrapper.findComponent({ name: 'TaskDueSoonCard' }).props('tasks')).toEqual([])
    expect(wrapper.findComponent({ name: 'TaskOverdueCard' }).props('tasks')).toEqual([])
    expect(wrapper.findComponent({ name: 'TeamPerformanceCard' }).props('teams')).toEqual([])
    expect(wrapper.findComponent({ name: 'TaskListCard' }).props('tasks')).toEqual([])
  })
})
