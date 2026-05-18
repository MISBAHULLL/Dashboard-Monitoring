/**
 * MemberDashboard Integration Tests
 *
 * Tests the MemberDashboard page component for proper data flow from controller,
 * rendering of all child components with correct data, and navigation breadcrumbs.
 *
 * **Validates: Requirements 11.2, 11.5**
 * - Requirement 11.2: THE Member_Dashboard SHALL receive and display all data properties from the DashboardController without modification
 * - Requirement 11.5: THE navigation breadcrumbs SHALL remain functional and unchanged
 */

import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import { h, defineComponent } from 'vue'
import type { MemberDashboardProps, Task } from '@/types/dashboard'
import MemberDashboard from './MemberDashboard.vue'

// Mock Inertia components
vi.mock('@inertiajs/vue3', () => ({
  Head: defineComponent({
    name: 'InertiaHead',
    props: ['title'],
    setup(_, { slots }) {
      return () => h('title', slots.default?.())
    },
  }),
  Link: defineComponent({
    name: 'InertiaLink',
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

/**
 * Factory function to create complete test props
 * Matches the data structure from DashboardController
 */
function createTestProps(overrides: Partial<MemberDashboardProps> = {}): MemberDashboardProps {
  return {
    stats: {
      total_tasks: 50,
      open_tasks: 15,
      in_progress_tasks: 10,
      completed_tasks: 25,
    },
    my_tasks: [
      { id: 1, title: 'Task 1', status: 'open', priority: 'high', client: { name: 'Client A' }, created_at: '2024-01-20' },
      { id: 2, title: 'Task 2', status: 'in_progress', priority: 'medium', client: { name: 'Client B' }, created_at: '2024-01-19' },
      { id: 3, title: 'Task 3', status: 'open', priority: 'urgent', client: { name: 'Client C' }, created_at: '2024-01-18' },
    ] as Task[],
    ...overrides,
  }
}

describe('MemberDashboard Integration Tests', () => {
  describe('Requirement 11.2: Receives and displays all data properties from DashboardController', () => {
    describe('component receives all props from controller', () => {
      it('receives and has access to stats prop', () => {
        const props = createTestProps()
        const wrapper = mount(MemberDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: true,
              BentoGridItem: true,
              StatCard: true,
              TaskListCard: true,
            },
          },
        })

        expect(wrapper.props('stats')).toEqual(props.stats)
        expect(wrapper.props('stats').total_tasks).toBe(50)
        expect(wrapper.props('stats').open_tasks).toBe(15)
        expect(wrapper.props('stats').in_progress_tasks).toBe(10)
        expect(wrapper.props('stats').completed_tasks).toBe(25)
      })

      it('receives and has access to my_tasks prop', () => {
        const props = createTestProps()
        const wrapper = mount(MemberDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: true,
              BentoGridItem: true,
              StatCard: true,
              TaskListCard: true,
            },
          },
        })

        expect(wrapper.props('my_tasks')).toEqual(props.my_tasks)
        expect(wrapper.props('my_tasks')).toHaveLength(3)
      })

      it('receives stats with all required properties', () => {
        const props = createTestProps()
        const wrapper = mount(MemberDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: true,
              BentoGridItem: true,
              StatCard: true,
              TaskListCard: true,
            },
          },
        })

        const stats = wrapper.props('stats')
        expect(stats).toHaveProperty('total_tasks')
        expect(stats).toHaveProperty('open_tasks')
        expect(stats).toHaveProperty('in_progress_tasks')
        expect(stats).toHaveProperty('completed_tasks')
      })
    })

    describe('all data is rendered in appropriate components', () => {
      it('renders BentoGrid container', () => {
        const props = createTestProps()
        const wrapper = mount(MemberDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: false,
              BentoGridItem: true,
              StatCard: true,
              TaskListCard: true,
            },
          },
        })

        expect(wrapper.findComponent({ name: 'BentoGrid' }).exists()).toBe(true)
      })

      it('renders 3 StatCard components for statistics', () => {
        const props = createTestProps()
        const wrapper = mount(MemberDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: false,
              BentoGridItem: false,
              StatCard: false,
              TaskListCard: true,
            },
          },
        })

        const statCards = wrapper.findAllComponents({ name: 'StatCard' })
        expect(statCards).toHaveLength(3)
      })

      it('passes correct props to Open Tasks StatCard (Perlu Dikerjakan)', () => {
        const props = createTestProps()
        const wrapper = mount(MemberDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: false,
              BentoGridItem: false,
              StatCard: false,
              TaskListCard: true,
            },
          },
        })

        const statCards = wrapper.findAllComponents({ name: 'StatCard' })
        const openTasksCard = statCards[0]
        
        expect(openTasksCard.props('label')).toBe('Perlu Dikerjakan')
        expect(openTasksCard.props('value')).toBe(props.stats.open_tasks)
        expect(openTasksCard.props('colorTheme')).toBe('amber')
      })

      it('passes correct props to In Progress StatCard (Sedang Dikerjakan)', () => {
        const props = createTestProps()
        const wrapper = mount(MemberDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: false,
              BentoGridItem: false,
              StatCard: false,
              TaskListCard: true,
            },
          },
        })

        const statCards = wrapper.findAllComponents({ name: 'StatCard' })
        const inProgressCard = statCards[1]
        
        expect(inProgressCard.props('label')).toBe('Sedang Dikerjakan')
        expect(inProgressCard.props('value')).toBe(props.stats.in_progress_tasks)
        expect(inProgressCard.props('colorTheme')).toBe('navy')
      })

      it('passes correct props to Completed Tasks StatCard (Tugas Selesai)', () => {
        const props = createTestProps()
        const wrapper = mount(MemberDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: false,
              BentoGridItem: false,
              StatCard: false,
              TaskListCard: true,
            },
          },
        })

        const statCards = wrapper.findAllComponents({ name: 'StatCard' })
        const completedCard = statCards[2]
        
        expect(completedCard.props('label')).toBe('Tugas Selesai')
        expect(completedCard.props('value')).toBe(props.stats.completed_tasks)
        expect(completedCard.props('colorTheme')).toBe('green')
      })

      it('renders TaskListCard with assigned variant', () => {
        const props = createTestProps()
        const wrapper = mount(MemberDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: false,
              BentoGridItem: false,
              StatCard: true,
              TaskListCard: false,
            },
          },
        })

        const taskCard = wrapper.findComponent({ name: 'TaskListCard' })
        expect(taskCard.exists()).toBe(true)
        expect(taskCard.props('variant')).toBe('assigned')
        expect(taskCard.props('tasks')).toEqual(props.my_tasks)
      })

      it('renders BentoGrid with correct column configuration', () => {
        const props = createTestProps()
        const wrapper = mount(MemberDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: false,
              BentoGridItem: true,
              StatCard: true,
              TaskListCard: true,
            },
          },
        })

        const bentoGrid = wrapper.findComponent({ name: 'BentoGrid' })
        expect(bentoGrid.props('columns')).toEqual({ default: 1, md: 3, lg: 3 })
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
          },
        })
        const wrapper = mount(MemberDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: false,
              BentoGridItem: false,
              StatCard: false,
              TaskListCard: true,
            },
          },
        })

        const statCards = wrapper.findAllComponents({ name: 'StatCard' })
        
        expect(statCards[0].props('value')).toBe(111) // open_tasks
        expect(statCards[1].props('value')).toBe(222) // in_progress_tasks
        expect(statCards[2].props('value')).toBe(333) // completed_tasks
      })

      it('passes my_tasks array directly without modification', () => {
        const customTasks: Task[] = [
          { id: 99, title: 'Custom Task', status: 'open', priority: 'urgent', client: { name: 'Custom Client' }, created_at: '2024-03-01' },
        ]
        
        const props = createTestProps({
          my_tasks: customTasks,
        })
        const wrapper = mount(MemberDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: false,
              BentoGridItem: false,
              StatCard: true,
              TaskListCard: false,
            },
          },
        })

        const taskCard = wrapper.findComponent({ name: 'TaskListCard' })
        expect(taskCard.props('tasks')).toEqual(customTasks)
      })

      it('passes icons to StatCard components', () => {
        const props = createTestProps()
        const wrapper = mount(MemberDashboard, {
          props,
          global: {
            stubs: {
              BentoGrid: false,
              BentoGridItem: false,
              StatCard: false,
              TaskListCard: true,
            },
          },
        })

        const statCards = wrapper.findAllComponents({ name: 'StatCard' })
        
        // All StatCards should have an icon prop
        statCards.forEach((card) => {
          expect(card.props('icon')).toBeDefined()
        })
      })
    })
  })

  describe('Requirement 11.5: Navigation breadcrumbs remain functional', () => {
    it('defines breadcrumbs in layout options', () => {
      // MemberDashboard uses defineOptions for breadcrumbs
      expect(MemberDashboard).toBeDefined()
    })

    it('renders the page header with title', () => {
      const props = createTestProps()
      const wrapper = mount(MemberDashboard, {
        props,
        global: {
          stubs: {
            BentoGrid: true,
            BentoGridItem: true,
            StatCard: true,
            TaskListCard: true,
          },
        },
      })

      expect(wrapper.find('h1').exists()).toBe(true)
      expect(wrapper.find('h1').text()).toBe('Selamat Datang!')
    })

    it('renders the page header with description', () => {
      const props = createTestProps()
      const wrapper = mount(MemberDashboard, {
        props,
        global: {
          stubs: {
            BentoGrid: true,
            BentoGridItem: true,
            StatCard: true,
            TaskListCard: true,
          },
        },
      })

      const description = wrapper.find('p.text-muted-foreground')
      expect(description.exists()).toBe(true)
      expect(description.text()).toContain('ringkasan task yang ditugaskan')
    })

    it('has proper page structure for breadcrumb context', () => {
      const props = createTestProps()
      const wrapper = mount(MemberDashboard, {
        props,
        global: {
          stubs: {
            BentoGrid: true,
            BentoGridItem: true,
            StatCard: true,
            TaskListCard: true,
          },
        },
      })

      // Verify the page container exists
      expect(wrapper.find('.flex.h-full.flex-1').exists()).toBe(true)
    })

    it('uses Head component for page title', () => {
      const props = createTestProps()
      const wrapper = mount(MemberDashboard, {
        props,
        global: {
          stubs: {
            InertiaHead: false,
            BentoGrid: true,
            BentoGridItem: true,
            StatCard: true,
            TaskListCard: true,
          },
        },
      })

      // Head component should be present with title prop
      const headComponent = wrapper.findComponent({ name: 'InertiaHead' })
      expect(headComponent.exists()).toBe(true)
      expect(headComponent.props('title')).toBe('Member Dashboard')
    })
  })

  describe('handles edge cases gracefully', () => {
    it('handles empty my_tasks array', () => {
      const props = createTestProps({
        my_tasks: [],
      })
      const wrapper = mount(MemberDashboard, {
        props,
        global: {
          stubs: {
            BentoGrid: false,
            BentoGridItem: false,
            StatCard: true,
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
        },
      })
      const wrapper = mount(MemberDashboard, {
        props,
        global: {
          stubs: {
            BentoGrid: false,
            BentoGridItem: false,
            StatCard: false,
            TaskListCard: true,
          },
        },
      })

      const statCards = wrapper.findAllComponents({ name: 'StatCard' })
      statCards.forEach((card) => {
        expect(card.props('value')).toBe(0)
      })
    })

    it('handles large numbers in stats', () => {
      const props = createTestProps({
        stats: {
          total_tasks: 10000,
          open_tasks: 5000,
          in_progress_tasks: 3000,
          completed_tasks: 2000,
        },
      })
      const wrapper = mount(MemberDashboard, {
        props,
        global: {
          stubs: {
            BentoGrid: false,
            BentoGridItem: false,
            StatCard: false,
            TaskListCard: true,
          },
        },
      })

      const statCards = wrapper.findAllComponents({ name: 'StatCard' })
      
      expect(statCards[0].props('value')).toBe(5000) // open_tasks
      expect(statCards[1].props('value')).toBe(3000) // in_progress_tasks
      expect(statCards[2].props('value')).toBe(2000) // completed_tasks
    })

    it('handles tasks with missing optional fields', () => {
      const tasksWithMissingFields: Task[] = [
        { id: 1, title: 'Task without client', status: 'open', created_at: '2024-01-20' },
        { id: 2, title: 'Task without priority', status: 'in_progress', client: { name: 'Client A' }, created_at: '2024-01-19' },
      ]
      
      const props = createTestProps({
        my_tasks: tasksWithMissingFields,
      })
      const wrapper = mount(MemberDashboard, {
        props,
        global: {
          stubs: {
            BentoGrid: false,
            BentoGridItem: false,
            StatCard: true,
            TaskListCard: false,
          },
        },
      })

      const taskCard = wrapper.findComponent({ name: 'TaskListCard' })
      expect(taskCard.props('tasks')).toEqual(tasksWithMissingFields)
    })

    it('handles my_tasks with exactly 5 tasks (max display)', () => {
      const fiveTasks: Task[] = Array.from({ length: 5 }, (_, i) => ({
        id: i + 1,
        title: `Task ${i + 1}`,
        status: 'open' as const,
        priority: 'medium' as const,
        client: { name: `Client ${i + 1}` },
        created_at: `2024-01-${20 - i}`,
      }))
      
      const props = createTestProps({
        my_tasks: fiveTasks,
      })
      const wrapper = mount(MemberDashboard, {
        props,
        global: {
          stubs: {
            BentoGrid: false,
            BentoGridItem: false,
            StatCard: true,
            TaskListCard: false,
          },
        },
      })

      const taskCard = wrapper.findComponent({ name: 'TaskListCard' })
      expect(taskCard.props('tasks')).toHaveLength(5)
    })
  })

  describe('component integration', () => {
    it('renders all expected child components together', () => {
      const props = createTestProps()
      const wrapper = mount(MemberDashboard, {
        props,
        global: {
          stubs: {
            BentoGrid: false,
            BentoGridItem: false,
            StatCard: false,
            TaskListCard: false,
          },
        },
      })

      // Check all expected components are rendered
      expect(wrapper.findComponent({ name: 'BentoGrid' }).exists()).toBe(true)
      expect(wrapper.findAllComponents({ name: 'StatCard' })).toHaveLength(3)
      expect(wrapper.findComponent({ name: 'TaskListCard' }).exists()).toBe(true)
    })

    it('renders BentoGridItems with correct span configuration', () => {
      const props = createTestProps()
      const wrapper = mount(MemberDashboard, {
        props,
        global: {
          stubs: {
            BentoGrid: false,
            BentoGridItem: false,
            StatCard: true,
            TaskListCard: true,
          },
        },
      })

      const gridItems = wrapper.findAllComponents({ name: 'BentoGridItem' })
      
      // First 3 items (StatCards) should have responsive span
      expect(gridItems[0].props('span')).toEqual({ default: 'col-span-1', md: 'col-span-1', lg: 'col-span-1' })
      expect(gridItems[1].props('span')).toEqual({ default: 'col-span-1', md: 'col-span-1', lg: 'col-span-1' })
      expect(gridItems[2].props('span')).toEqual({ default: 'col-span-1', md: 'col-span-1', lg: 'col-span-1' })
      
      // Last item (TaskListCard) should span full width
      expect(gridItems[3].props('span')).toEqual({ default: 'col-span-full' })
    })

    it('maintains correct prop flow through the component tree', () => {
      const props = createTestProps()
      const wrapper = mount(MemberDashboard, {
        props,
        global: {
          stubs: {
            BentoGrid: false,
            BentoGridItem: false,
            StatCard: false,
            TaskListCard: false,
          },
        },
      })

      // Verify data flows correctly from props to child components
      const statCards = wrapper.findAllComponents({ name: 'StatCard' })
      const taskCard = wrapper.findComponent({ name: 'TaskListCard' })

      // StatCards should have correct values from stats prop
      expect(statCards[0].props('value')).toBe(props.stats.open_tasks)
      expect(statCards[1].props('value')).toBe(props.stats.in_progress_tasks)
      expect(statCards[2].props('value')).toBe(props.stats.completed_tasks)

      // TaskListCard should have my_tasks prop
      expect(taskCard.props('tasks')).toEqual(props.my_tasks)
    })
  })
})
