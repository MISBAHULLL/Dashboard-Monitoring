/**
 * TaskListCard Component Property-Based Tests
 *
 * Tests universal correctness properties for task list status and priority badge color mapping.
 * Uses property-based testing with fast-check to verify that for all possible
 * status and priority values, the component applies correct color classes.
 *
 * Property 2: Status Badge Color Mapping
 * - Generate tasks with random status values
 * - Verify badge element has correct color class
 *
 * Property 3: Priority Badge Color Mapping
 * - Generate tasks with random priority values
 * - Verify badge element has correct color class
 *
 * @see Requirements 7.3, 9.3, 9.4
 */

import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import * as fc from 'fast-check'
import TaskListCard from './TaskListCard.vue'
import type { Task, TaskStatus, TaskPriority, TaskListVariant } from '@/types/dashboard'

/**
 * Helper to generate a random task for testing
 */
const generateTask = (id: number, overrides: Partial<Task> = {}): Task => ({
  id,
  title: `Task ${id}`,
  status: 'open',
  priority: 'medium',
  client: { id, name: `Client ${id}` },
  created_at: new Date(2024, 0, id).toISOString(),
  ...overrides,
})

/**
 * Helper to generate an array of tasks
 */
const generateTasks = (count: number, overrides: Partial<Task> = {}): Task[] =>
  Array.from({ length: count }, (_, i) => generateTask(i + 1, overrides))

/**
 * Status to color mapping
 * Defines the expected color for each task status
 * - open: amber
 * - in_progress: blue
 * - revision: red
 * - completed: emerald (green)
 */
const STATUS_COLOR_MAPPING: Record<TaskStatus, {
  color: string
  bgClasses: string[]
  textClasses: string[]
  borderClasses: string[]
}> = {
  open: {
    color: 'amber',
    bgClasses: ['bg-amber-100', 'dark:bg-amber-900/30'],
    textClasses: ['text-amber-800', 'dark:text-amber-400'],
    borderClasses: ['border-amber-200', 'dark:border-amber-900/50'],
  },
  in_progress: {
    color: 'blue',
    bgClasses: ['bg-blue-100', 'dark:bg-blue-900/30'],
    textClasses: ['text-blue-800', 'dark:text-blue-400'],
    borderClasses: ['border-blue-200', 'dark:border-blue-900/50'],
  },
  revision: {
    color: 'red',
    bgClasses: ['bg-red-100', 'dark:bg-red-900/30'],
    textClasses: ['text-red-800', 'dark:text-red-400'],
    borderClasses: ['border-red-200', 'dark:border-red-900/50'],
  },
  completed: {
    color: 'emerald',
    bgClasses: ['bg-emerald-100', 'dark:bg-emerald-900/30'],
    textClasses: ['text-emerald-800', 'dark:text-emerald-400'],
    borderClasses: ['border-emerald-200', 'dark:border-emerald-900/50'],
  },
}

/**
 * Valid task status values
 */
const VALID_STATUSES: TaskStatus[] = ['open', 'in_progress', 'revision', 'completed']

/**
 * Arbitrary for generating valid TaskStatus values
 */
const taskStatusArbitrary = fc.constantFrom(...VALID_STATUSES)

/**
 * Arbitrary for generating tasks with random status
 * Note: fc.date() can generate invalid dates in some environments, so we generate ISO strings directly
 */
const taskWithStatusArbitrary = fc.record({
  id: fc.integer({ min: 1, max: 10000 }),
  title: fc.string({ minLength: 1, maxLength: 100 }),
  status: taskStatusArbitrary,
  priority: fc.constantFrom('urgent', 'high', 'medium', 'low'),
  client: fc.record({
    id: fc.integer({ min: 1, max: 1000 }),
    name: fc.string({ minLength: 1, maxLength: 50 }),
  }),
  created_at: fc.tuple(
    fc.integer({ min: 2020, max: 2025 }),
    fc.integer({ min: 1, max: 12 }),
    fc.integer({ min: 1, max: 28 })
  ).map(([year, month, day]) => `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}T00:00:00.000Z`),
}) as fc.Arbitrary<Task>

/**
 * Arbitrary for generating arrays of tasks with varying statuses
 */
const taskArrayArbitrary = fc.array(taskWithStatusArbitrary, {
  minLength: 1,
  maxLength: 20,
})

describe('TaskListCard Property-Based Tests', () => {
  describe('Property 2: Status Badge Color Mapping', () => {
    /**
     * Property: For any task with any valid status, the status badge must have
     * the correct background color class based on the status.
     *
     * @validates Requirements 7.3, 9.4
     */
    it('applies correct background class for any status', () => {
      fc.assert(
        fc.property(
          taskStatusArbitrary,
          fc.string({ minLength: 1, maxLength: 50 }),
          (status, title) => {
            const tasks = [generateTask(1, { status: status as TaskStatus, title })]
            const wrapper = mount(TaskListCard, {
              props: {
                variant: 'recent',
                tasks,
              },
            })

            // Find the status badge (span with rounded-full class)
            const statusBadge = wrapper.find('span.rounded-full')
            expect(statusBadge.exists()).toBe(true)

            const classes = statusBadge.classes()
            const expectedBgClasses = STATUS_COLOR_MAPPING[status].bgClasses

            // At least one background class should be present
            const hasExpectedBackground = expectedBgClasses.some(bgClass =>
              classes.includes(bgClass)
            )
            expect(hasExpectedBackground).toBe(true)
          }
        )
      )
    })

    /**
     * Property: For any task with any valid status, the status badge must have
     * the correct text color class based on the status.
     *
     * @validates Requirements 7.3, 9.4
     */
    it('applies correct text color class for any status', () => {
      fc.assert(
        fc.property(
          taskStatusArbitrary,
          fc.string({ minLength: 1, maxLength: 50 }),
          (status, title) => {
            const tasks = [generateTask(1, { status: status as TaskStatus, title })]
            const wrapper = mount(TaskListCard, {
              props: {
                variant: 'recent',
                tasks,
              },
            })

            const statusBadge = wrapper.find('span.rounded-full')
            expect(statusBadge.exists()).toBe(true)

            const classes = statusBadge.classes()
            const expectedTextClasses = STATUS_COLOR_MAPPING[status].textClasses

            // At least one text color class should be present
            const hasExpectedTextColor = expectedTextClasses.some(textClass =>
              classes.includes(textClass)
            )
            expect(hasExpectedTextColor).toBe(true)
          }
        )
      )
    })

    /**
     * Property: For any task with any valid status, the status badge must have
     * the correct border color class based on the status.
     *
     * @validates Requirements 7.3, 9.4
     */
    it('applies correct border class for any status', () => {
      fc.assert(
        fc.property(
          taskStatusArbitrary,
          fc.string({ minLength: 1, maxLength: 50 }),
          (status, title) => {
            const tasks = [generateTask(1, { status: status as TaskStatus, title })]
            const wrapper = mount(TaskListCard, {
              props: {
                variant: 'recent',
                tasks,
              },
            })

            const statusBadge = wrapper.find('span.rounded-full')
            expect(statusBadge.exists()).toBe(true)

            const classes = statusBadge.classes()
            const expectedBorderClasses = STATUS_COLOR_MAPPING[status].borderClasses

            // At least one border class should be present
            const hasExpectedBorder = expectedBorderClasses.some(borderClass =>
              classes.includes(borderClass)
            )
            expect(hasExpectedBorder).toBe(true)
          }
        )
      )
    })

    /**
     * Property: For any task with any valid status, the status badge must have
     * all the expected color classes (background, text, border) consistently applied.
     *
     * This is a comprehensive property that verifies the entire status-to-color mapping.
     *
     * @validates Requirements 7.3, 9.4
     */
    it('applies all correct color classes consistently for any status', () => {
      fc.assert(
        fc.property(
          taskStatusArbitrary,
          fc.integer({ min: 1, max: 10000 }),
          (status, taskId) => {
            const tasks = [generateTask(taskId, { status: status as TaskStatus })]
            const wrapper = mount(TaskListCard, {
              props: {
                variant: 'recent',
                tasks,
              },
            })

            const expectedClasses = STATUS_COLOR_MAPPING[status]
            const statusBadge = wrapper.find('span.rounded-full')
            expect(statusBadge.exists()).toBe(true)

            const classes = statusBadge.classes()

            // Verify background class
            expect(
              expectedClasses.bgClasses.some(bg => classes.includes(bg))
            ).toBe(true)

            // Verify text color class
            expect(
              expectedClasses.textClasses.some(tc => classes.includes(tc))
            ).toBe(true)

            // Verify border class
            expect(
              expectedClasses.borderClasses.some(bc => classes.includes(bc))
            ).toBe(true)
          }
        )
      )
    })

    /**
     * Property: For any array of tasks with varying statuses, each status badge
     * should have the correct color classes corresponding to its status.
     *
     * @validates Requirements 7.3, 9.4
     */
    it('applies correct color classes for tasks with varying statuses', () => {
      fc.assert(
        fc.property(
          taskArrayArbitrary,
          (tasks) => {
            const wrapper = mount(TaskListCard, {
              props: {
                variant: 'recent',
                tasks,
              },
            })

            // Find all status badges
            const statusBadges = wrapper.findAll('span.rounded-full')

            // For each task, verify its status badge has correct colors
            tasks.forEach((task, index) => {
              if (index < statusBadges.length) {
                const badge = statusBadges[index]
                const classes = badge.classes()
                const expectedClasses = STATUS_COLOR_MAPPING[task.status]

                // Verify at least one color-related class matches
                const hasCorrectBg = expectedClasses.bgClasses.some(bg =>
                  classes.includes(bg)
                )
                expect(hasCorrectBg).toBe(true)
              }
            })
          }
        )
      )
    })

    /**
     * Property: For any valid status, the badge should have the correct structure
     * (rounded-full, border, px-2.5, py-0.5, text-xs, font-semibold, capitalize).
     *
     * @validates Requirements 7.3, 9.4
     */
    it('status badge has correct structural classes for any status', () => {
      fc.assert(
        fc.property(
          taskStatusArbitrary,
          (status) => {
            const tasks = [generateTask(1, { status: status as TaskStatus })]
            const wrapper = mount(TaskListCard, {
              props: {
                variant: 'recent',
                tasks,
              },
            })

            const statusBadge = wrapper.find('span.rounded-full')
            expect(statusBadge.exists()).toBe(true)

            const classes = statusBadge.classes()

            // Verify structural classes
            expect(classes).toContain('inline-flex')
            expect(classes).toContain('items-center')
            expect(classes).toContain('rounded-full')
            expect(classes).toContain('border')
            expect(classes).toContain('px-2.5')
            expect(classes).toContain('py-0.5')
            expect(classes).toContain('text-xs')
            expect(classes).toContain('font-semibold')
            expect(classes).toContain('capitalize')
          }
        )
      )
    })

    /**
     * Property: For any status, the status text should be formatted correctly
     * (underscores replaced with spaces).
     *
     * @validates Requirements 7.3, 9.4
     */
    it('formats status text correctly for any status', () => {
      fc.assert(
        fc.property(
          taskStatusArbitrary,
          (status) => {
            const tasks = [generateTask(1, { status: status as TaskStatus })]
            const wrapper = mount(TaskListCard, {
              props: {
                variant: 'recent',
                tasks,
              },
            })

            const statusBadge = wrapper.find('span.rounded-full')
            expect(statusBadge.exists()).toBe(true)

            // Status text should have underscores replaced with spaces
            const expectedText = status.replace('_', ' ')
            expect(statusBadge.text()).toBe(expectedText)
          }
        )
      )
    })
  })

  describe('Property: Status Badge Rendering Across Variants', () => {
    /**
     * Property: For any task with any status, the status badge should be rendered
     * consistently across both 'recent' and 'assigned' variants (for non-completed tasks).
     *
     * @validates Requirements 7.3, 9.4
     */
    it('applies same color classes across both variants for non-completed tasks', () => {
      fc.assert(
        fc.property(
          fc.constantFrom('open', 'in_progress', 'revision'), // Exclude 'completed'
          fc.integer({ min: 1, max: 10000 }),
          (status, taskId) => {
            const tasks = [generateTask(taskId, { status: status as TaskStatus })]

            // Test with 'recent' variant
            const recentWrapper = mount(TaskListCard, {
              props: {
                variant: 'recent',
                tasks,
              },
            })
            const recentBadge = recentWrapper.find('span.rounded-full')
            const recentClasses = recentBadge.classes()

            // Test with 'assigned' variant
            const assignedWrapper = mount(TaskListCard, {
              props: {
                variant: 'assigned',
                tasks,
              },
            })
            const assignedBadge = assignedWrapper.find('span.rounded-full')
            const assignedClasses = assignedBadge.classes()

            // Both should have the same color-related classes
            const expectedClasses = STATUS_COLOR_MAPPING[status]

            // Verify both variants have correct background
            expect(
              expectedClasses.bgClasses.some(bg => recentClasses.includes(bg))
            ).toBe(true)
            expect(
              expectedClasses.bgClasses.some(bg => assignedClasses.includes(bg))
            ).toBe(true)
          }
        )
      )
    })
  })

  describe('Property 5: Task List Filtering', () => {
    /**
     * Arbitrary for generating tasks with random statuses including completed
     */
    const taskWithMixedStatusArbitrary = fc.record({
      id: fc.integer({ min: 1, max: 10000 }),
      title: fc.string({ minLength: 1, maxLength: 50 }),
      status: fc.constantFrom('open', 'in_progress', 'revision', 'completed') as fc.Arbitrary<TaskStatus>,
      priority: fc.constantFrom('urgent', 'high', 'medium', 'low'),
      client: fc.record({
        id: fc.integer({ min: 1, max: 1000 }),
        name: fc.string({ minLength: 1, maxLength: 30 }),
      }),
      created_at: fc.string({ minLength: 1, maxLength: 1 }).map(() => new Date(2024, 0, 1).toISOString()),
    }) as fc.Arbitrary<Task>

    /**
     * Arbitrary for generating arrays of tasks with mixed statuses
     */
    const taskArrayWithMixedStatusArbitrary = fc.array(taskWithMixedStatusArbitrary, {
      minLength: 1,
      maxLength: 30,
    })

    /**
     * Property: For the 'assigned' variant, completed tasks should be filtered out
     * and not displayed at all.
     *
     * This is the core property ensuring Requirement 9.1: Member Dashboard displays
     * assigned tasks that are not completed.
     *
     * @validates Requirements 9.1
     */
    it('filters out completed tasks for assigned variant', () => {
      fc.assert(
        fc.property(
          taskArrayWithMixedStatusArbitrary,
          (tasks) => {
            const wrapper = mount(TaskListCard, {
              props: {
                variant: 'assigned',
                tasks,
              },
            })

            // Find all task rows (exclude empty state)
            const taskRows = wrapper.findAll('tbody tr').filter(row => {
              return !row.find('td[colspan]').exists()
            })

            // Extract rendered task titles
            const renderedTitles = taskRows.map(row => {
              const cells = row.findAll('td')
              return cells[1]?.text().trim() || ''
            })

            // No completed tasks should be in rendered output
            const completedTaskTitles = tasks
              .filter(t => t.status === 'completed')
              .map(t => t.title)

            completedTaskTitles.forEach(title => {
              expect(renderedTitles).not.toContain(title)
            })
          }
        )
      )
    })

    /**
     * Property: For the 'assigned' variant, only non-completed tasks are rendered.
     * The rendered task count should equal the count of non-completed tasks in input.
     *
     * @validates Requirements 9.1
     */
    it('renders exactly the non-completed tasks for assigned variant', () => {
      fc.assert(
        fc.property(
          taskArrayWithMixedStatusArbitrary,
          (tasks) => {
            const wrapper = mount(TaskListCard, {
              props: {
                variant: 'assigned',
                tasks,
              },
            })

            // Find all task rows (exclude empty state)
            const taskRows = wrapper.findAll('tbody tr').filter(row => {
              return !row.find('td[colspan]').exists()
            })

            // Count non-completed tasks in input
            const nonCompletedTasks = tasks.filter(t => t.status !== 'completed')

            // Rendered count should match non-completed count
            expect(taskRows.length).toBe(nonCompletedTasks.length)
          }
        )
      )
    })

    /**
     * Property: For the 'assigned' variant, if all tasks are completed,
     * the empty state message should be displayed.
     *
     * @validates Requirements 9.1, 9.6
     */
    it('shows empty state when all tasks are completed for assigned variant', () => {
      fc.assert(
        fc.property(
          fc.array(
            fc.record({
              id: fc.integer({ min: 1, max: 10000 }),
              title: fc.string({ minLength: 1, maxLength: 50 }),
              status: fc.constant('completed') as fc.Arbitrary<TaskStatus>,
              priority: fc.constantFrom('urgent', 'high', 'medium', 'low'),
              client: fc.record({
                id: fc.integer({ min: 1, max: 1000 }),
                name: fc.string({ minLength: 1, maxLength: 30 }),
              }),
              created_at: fc.string({ minLength: 1, maxLength: 1 }).map(() => new Date(2024, 0, 1).toISOString()),
            }) as fc.Arbitrary<Task>,
            { minLength: 1, maxLength: 10 }
          ),
          (tasks) => {
            const wrapper = mount(TaskListCard, {
              props: {
                variant: 'assigned',
                tasks,
              },
            })

            // Should show empty state message
            expect(wrapper.text()).toContain('Hebat! Anda tidak memiliki task yang tertunda. 🎉')
          }
        )
      )
    })

    /**
     * Property: For the 'recent' variant, completed tasks ARE displayed
     * (no filtering should occur).
     *
     * @validates Requirements 7.1, 7.2
     */
    it('displays completed tasks for recent variant (no filtering)', () => {
      fc.assert(
        fc.property(
          fc.array(
            fc.record({
              id: fc.integer({ min: 1, max: 10000 }),
              title: fc.string({ minLength: 1, maxLength: 50 }),
              status: fc.constant('completed') as fc.Arbitrary<TaskStatus>,
              priority: fc.constantFrom('urgent', 'high', 'medium', 'low'),
              client: fc.record({
                id: fc.integer({ min: 1, max: 1000 }),
                name: fc.string({ minLength: 1, maxLength: 30 }),
              }),
              created_at: fc.string({ minLength: 1, maxLength: 1 }).map(() => new Date(2024, 0, 1).toISOString()),
            }) as fc.Arbitrary<Task>,
            { minLength: 1, maxLength: 5 }
          ),
          (tasks) => {
            const wrapper = mount(TaskListCard, {
              props: {
                variant: 'recent',
                tasks,
              },
            })

            // Find all task rows
            const taskRows = wrapper.findAll('tbody tr').filter(row => {
              return !row.find('td[colspan]').exists()
            })

            // All tasks should be rendered (no filtering)
            expect(taskRows.length).toBe(tasks.length)

            // Each task title should appear in output
            tasks.forEach(task => {
              expect(wrapper.text()).toContain(task.title)
            })
          }
        )
      )
    })

    /**
     * Property: For any mix of tasks, the 'assigned' variant should only render
     * tasks with status 'open', 'in_progress', or 'revision'.
     *
     * @validates Requirements 9.1
     */
    it('only renders open, in_progress, and revision tasks for assigned variant', () => {
      fc.assert(
        fc.property(
          taskArrayWithMixedStatusArbitrary,
          (tasks) => {
            const wrapper = mount(TaskListCard, {
              props: {
                variant: 'assigned',
                tasks,
              },
            })

            // Find all status badges in rendered output
            const statusBadges = wrapper.findAll('span.rounded-full')

            // Each rendered status should NOT be 'completed'
            statusBadges.forEach(badge => {
              const statusText = badge.text()
              expect(statusText).not.toBe('completed')
            })
          }
        )
      )
    })

    /**
     * Property: For the 'assigned' variant, the rendered task titles should exactly
     * match the titles of non-completed tasks.
     *
     * @validates Requirements 9.1
     */
    it('renders exact task titles for non-completed tasks in assigned variant', () => {
      fc.assert(
        fc.property(
          taskArrayWithMixedStatusArbitrary,
          (tasks) => {
            const wrapper = mount(TaskListCard, {
              props: {
                variant: 'assigned',
                tasks,
              },
            })

            // Find all task rows
            const taskRows = wrapper.findAll('tbody tr').filter(row => {
              return !row.find('td[colspan]').exists()
            })

            // Get non-completed tasks
            const nonCompletedTasks = tasks.filter(t => t.status !== 'completed')

            // Extract rendered titles
            const renderedTitles = taskRows.map(row => {
              const cells = row.findAll('td')
              return cells[1]?.text().trim() || ''
            })

            // Get expected titles
            const expectedTitles = nonCompletedTasks.map(t => t.title.trim())

            // Sort both arrays for comparison (order may vary)
            const sortedRendered = [...renderedTitles].sort()
            const sortedExpected = [...expectedTitles].sort()

            // They should match
            expect(sortedRendered).toEqual(sortedExpected)
          }
        )
      )
    })
  })

  describe('Property: Distinct Status Colors', () => {
    /**
     * Property: Each status should produce a distinct color mapping.
     * Different statuses should have different color classes.
     *
     * @validates Requirements 7.3, 9.4
     */
    it('produces distinct color classes for different statuses', () => {
      const statusColorSets = new Map<TaskStatus, Set<string>>()

      VALID_STATUSES.forEach((status) => {
        const tasks = [generateTask(1, { status })]
        const wrapper = mount(TaskListCard, {
          props: {
            variant: 'recent',
            tasks,
          },
        })

        const statusBadge = wrapper.find('span.rounded-full')
        const classes = statusBadge.classes()

        // Extract color-related classes
        const colorClasses = classes.filter(c =>
          c.includes('bg-') || c.includes('text-') || c.includes('border-')
        )

        statusColorSets.set(status, new Set(colorClasses))
      })

      // Each status should have unique color classes
      // At minimum, the background class should be unique for each status
      const bgClasses = VALID_STATUSES.map(status => {
        const classes = statusColorSets.get(status)!
        return Array.from(classes).find(c => c.startsWith('bg-') && !c.includes('dark:'))
      })

      // All background classes should be unique (4 statuses = 4 unique bg classes)
      const uniqueBgClasses = new Set(bgClasses)
      expect(uniqueBgClasses.size).toBe(VALID_STATUSES.length)
    })

    /**
     * Property: Status color mapping should be consistent across multiple renders.
     *
     * @validates Requirements 7.3, 9.4
     */
    it('produces consistent color classes across multiple renders', () => {
      fc.assert(
        fc.property(
          taskStatusArbitrary,
          fc.integer({ min: 1, max: 5 }),
          (status, renderCount) => {
            const tasks = [generateTask(1, { status: status as TaskStatus })]

            // Render multiple times
            const allClasses: string[][] = []
            for (let i = 0; i < renderCount; i++) {
              const wrapper = mount(TaskListCard, {
                props: {
                  variant: 'recent',
                  tasks,
                },
              })
              const statusBadge = wrapper.find('span.rounded-full')
              allClasses.push(statusBadge.classes())
            }

            // All renders should produce identical class lists
            const firstClassList = allClasses[0]
            allClasses.forEach(classList => {
              expect(classList).toEqual(firstClassList)
            })
          }
        )
      )
    })
  })
})

  describe('Property 3: Priority Badge Color Mapping', () => {
    /**
     * Priority to color mapping
     * Defines the expected color for each task priority
     * - urgent: red
     * - high: amber
     * - medium: blue
     * - low: slate
     *
     * @validates Requirements 9.3
     */
    const PRIORITY_COLOR_MAPPING: Record<TaskPriority, {
      color: string
      textClasses: string[]
      borderClasses: string[]
    }> = {
      urgent: {
        color: 'red',
        textClasses: ['text-red-600', 'dark:text-red-400'],
        borderClasses: ['border-red-200', 'dark:border-red-900/50'],
      },
      high: {
        color: 'amber',
        textClasses: ['text-amber-600', 'dark:text-amber-400'],
        borderClasses: ['border-amber-200', 'dark:border-amber-900/50'],
      },
      medium: {
        color: 'blue',
        textClasses: ['text-blue-600', 'dark:text-blue-400'],
        borderClasses: ['border-blue-200', 'dark:border-blue-900/50'],
      },
      low: {
        color: 'slate',
        textClasses: ['text-slate-600', 'dark:text-slate-400'],
        borderClasses: ['border-slate-200', 'dark:border-slate-700'],
      },
    }

    /**
     * Valid task priority values
     */
    const VALID_PRIORITIES: TaskPriority[] = ['urgent', 'high', 'medium', 'low']

    /**
     * Arbitrary for generating valid TaskPriority values
     */
    const taskPriorityArbitrary = fc.constantFrom(...VALID_PRIORITIES)

    /**
     * Property: For any task with any valid priority, the priority badge must have
     * the correct text color class based on the priority.
     *
     * @validates Requirements 9.3
     */
    it('applies correct text color class for any priority', () => {
      fc.assert(
        fc.property(
          taskPriorityArbitrary,
          fc.string({ minLength: 1, maxLength: 50 }),
          (priority, title) => {
            const tasks = [generateTask(1, { priority: priority as TaskPriority, title, status: 'open' })]
            const wrapper = mount(TaskListCard, {
              props: {
                variant: 'assigned',
                tasks,
              },
            })

            // Find the priority badge (span with rounded-md class)
            const priorityBadge = wrapper.find('span.rounded-md')
            expect(priorityBadge.exists()).toBe(true)

            const classes = priorityBadge.classes()
            const expectedTextClasses = PRIORITY_COLOR_MAPPING[priority].textClasses

            // At least one text color class should be present
            const hasExpectedTextColor = expectedTextClasses.some(textClass =>
              classes.includes(textClass)
            )
            expect(hasExpectedTextColor).toBe(true)
          }
        )
      )
    })

    /**
     * Property: For any task with any valid priority, the priority badge must have
     * the correct border color class based on the priority.
     *
     * @validates Requirements 9.3
     */
    it('applies correct border class for any priority', () => {
      fc.assert(
        fc.property(
          taskPriorityArbitrary,
          fc.string({ minLength: 1, maxLength: 50 }),
          (priority, title) => {
            const tasks = [generateTask(1, { priority: priority as TaskPriority, title, status: 'open' })]
            const wrapper = mount(TaskListCard, {
              props: {
                variant: 'assigned',
                tasks,
              },
            })

            const priorityBadge = wrapper.find('span.rounded-md')
            expect(priorityBadge.exists()).toBe(true)

            const classes = priorityBadge.classes()
            const expectedBorderClasses = PRIORITY_COLOR_MAPPING[priority].borderClasses

            // At least one border class should be present
            const hasExpectedBorder = expectedBorderClasses.some(borderClass =>
              classes.includes(borderClass)
            )
            expect(hasExpectedBorder).toBe(true)
          }
        )
      )
    })

    /**
     * Property: For any task with any valid priority, the priority badge must have
     * all the expected color classes (text, border) consistently applied.
     *
     * This is a comprehensive property that verifies the entire priority-to-color mapping.
     *
     * @validates Requirements 9.3
     */
    it('applies all correct color classes consistently for any priority', () => {
      fc.assert(
        fc.property(
          taskPriorityArbitrary,
          fc.integer({ min: 1, max: 10000 }),
          (priority, taskId) => {
            const tasks = [generateTask(taskId, { priority: priority as TaskPriority, status: 'open' })]
            const wrapper = mount(TaskListCard, {
              props: {
                variant: 'assigned',
                tasks,
              },
            })

            const expectedClasses = PRIORITY_COLOR_MAPPING[priority]
            const priorityBadge = wrapper.find('span.rounded-md')
            expect(priorityBadge.exists()).toBe(true)

            const classes = priorityBadge.classes()

            // Verify text color class
            expect(
              expectedClasses.textClasses.some(tc => classes.includes(tc))
            ).toBe(true)

            // Verify border class
            expect(
              expectedClasses.borderClasses.some(bc => classes.includes(bc))
            ).toBe(true)
          }
        )
      )
    })

    /**
     * Property: For any array of tasks with varying priorities, each priority badge
     * should have the correct color classes corresponding to its priority.
     *
     * @validates Requirements 9.3
     */
    it('applies correct color classes for tasks with varying priorities', () => {
      const taskWithPriorityArbitrary = fc.record({
        id: fc.integer({ min: 1, max: 10000 }),
        title: fc.string({ minLength: 1, maxLength: 100 }),
        status: fc.constantFrom('open', 'in_progress', 'revision') as fc.Arbitrary<TaskStatus>,
        priority: taskPriorityArbitrary,
        client: fc.record({
          id: fc.integer({ min: 1, max: 1000 }),
          name: fc.string({ minLength: 1, maxLength: 50 }),
        }),
        created_at: fc.tuple(
          fc.integer({ min: 2020, max: 2025 }),
          fc.integer({ min: 1, max: 12 }),
          fc.integer({ min: 1, max: 28 })
        ).map(([year, month, day]) => `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}T00:00:00.000Z`),
      }) as fc.Arbitrary<Task>

      const taskArrayWithPrioritiesArbitrary = fc.array(taskWithPriorityArbitrary, {
        minLength: 1,
        maxLength: 20,
      })

      fc.assert(
        fc.property(
          taskArrayWithPrioritiesArbitrary,
          (tasks) => {
            const wrapper = mount(TaskListCard, {
              props: {
                variant: 'assigned',
                tasks,
              },
            })

            // Find all priority badges (exclude status badges which have rounded-full)
            const priorityBadges = wrapper.findAll('span.rounded-md')

            // For each task with a priority, verify its badge has correct colors
            const tasksWithPriorities = tasks.filter(t => t.priority)
            tasksWithPriorities.forEach((task, index) => {
              if (index < priorityBadges.length) {
                const badge = priorityBadges[index]
                const classes = badge.classes()
                const expectedClasses = PRIORITY_COLOR_MAPPING[task.priority!]

                // Verify at least one text color class matches
                const hasCorrectTextColor = expectedClasses.textClasses.some(tc =>
                  classes.includes(tc)
                )
                expect(hasCorrectTextColor).toBe(true)

                // Verify at least one border class matches
                const hasCorrectBorder = expectedClasses.borderClasses.some(bc =>
                  classes.includes(bc)
                )
                expect(hasCorrectBorder).toBe(true)
              }
            })
          }
        )
      )
    })

    /**
     * Property: For any valid priority, the badge should have the correct structure
     * (rounded-md, border, px-2, py-0.5, text-xs, font-semibold, capitalize).
     *
     * @validates Requirements 9.3
     */
    it('priority badge has correct structural classes for any priority', () => {
      fc.assert(
        fc.property(
          taskPriorityArbitrary,
          (priority) => {
            const tasks = [generateTask(1, { priority: priority as TaskPriority, status: 'open' })]
            const wrapper = mount(TaskListCard, {
              props: {
                variant: 'assigned',
                tasks,
              },
            })

            const priorityBadge = wrapper.find('span.rounded-md')
            expect(priorityBadge.exists()).toBe(true)

            const classes = priorityBadge.classes()

            // Verify structural classes
            expect(classes).toContain('inline-flex')
            expect(classes).toContain('items-center')
            expect(classes).toContain('rounded-md')
            expect(classes).toContain('border')
            expect(classes).toContain('px-2')
            expect(classes).toContain('py-0.5')
            expect(classes).toContain('text-xs')
            expect(classes).toContain('font-semibold')
            expect(classes).toContain('capitalize')
          }
        )
      )
    })

    /**
     * Property: For any priority, the priority text should be displayed correctly
     * (the priority value as-is, with capitalize class).
     *
     * @validates Requirements 9.3
     */
    it('displays priority text correctly for any priority', () => {
      fc.assert(
        fc.property(
          taskPriorityArbitrary,
          (priority) => {
            const tasks = [generateTask(1, { priority: priority as TaskPriority, status: 'open' })]
            const wrapper = mount(TaskListCard, {
              props: {
                variant: 'assigned',
                tasks,
              },
            })

            const priorityBadge = wrapper.find('span.rounded-md')
            expect(priorityBadge.exists()).toBe(true)

            // Priority text should match the priority value
            expect(priorityBadge.text()).toBe(priority)
          }
        )
      )
    })

    /**
     * Property: Each priority should produce a distinct color mapping.
     * Different priorities should have different color classes.
     *
     * @validates Requirements 9.3
     */
    it('produces distinct color classes for different priorities', () => {
      const priorityColorSets = new Map<TaskPriority, Set<string>>()

      VALID_PRIORITIES.forEach((priority) => {
        const tasks = [generateTask(1, { priority, status: 'open' })]
        const wrapper = mount(TaskListCard, {
          props: {
            variant: 'assigned',
            tasks,
          },
        })

        const priorityBadge = wrapper.find('span.rounded-md')
        const classes = priorityBadge.classes()

        // Extract color-related classes (text and border)
        const colorClasses = classes.filter(c =>
          c.includes('text-') || c.includes('border-')
        )

        priorityColorSets.set(priority, new Set(colorClasses))
      })

      // Verify each priority has unique color combinations
      // Check that each priority has distinct text color (light mode)
      const lightModeTextClasses: string[] = []
      VALID_PRIORITIES.forEach(priority => {
        const classes = priorityColorSets.get(priority)!
        const textClass = Array.from(classes).find(c => 
          c.startsWith('text-') && 
          !c.includes('dark:') &&
          c.match(/text-(red|amber|blue|slate)-/)
        )
        if (textClass) {
          lightModeTextClasses.push(textClass)
        }
      })

      // All light mode text color classes should be unique (4 priorities = 4 unique text classes)
      const uniqueTextClasses = new Set(lightModeTextClasses)
      expect(uniqueTextClasses.size).toBe(VALID_PRIORITIES.length)
    })

    /**
     * Property: For any valid priority, the priority badge should render
     * successfully without errors.
     *
     * @validates Requirements 9.3
     */
    it('renders successfully for any valid priority', () => {
      fc.assert(
        fc.property(
          taskPriorityArbitrary,
          fc.string({ minLength: 1, maxLength: 100 }),
          (priority, title) => {
            // Should not throw
            const tasks = [generateTask(1, { priority: priority as TaskPriority, title, status: 'open' })]
            const wrapper = mount(TaskListCard, {
              props: {
                variant: 'assigned',
                tasks,
              },
            })

            // Should render a priority badge
            const priorityBadge = wrapper.find('span.rounded-md')
            expect(priorityBadge.exists()).toBe(true)

            // Should have some classes (not empty)
            const classes = priorityBadge.classes()
            expect(classes.length).toBeGreaterThan(0)
          }
        )
      )
    })

    /**
     * Property: Priority color mapping should be consistent across multiple renders.
     *
     * @validates Requirements 9.3
     */
    it('produces consistent color classes across multiple renders', () => {
      fc.assert(
        fc.property(
          taskPriorityArbitrary,
          fc.integer({ min: 1, max: 5 }),
          (priority, renderCount) => {
            const tasks = [generateTask(1, { priority: priority as TaskPriority, status: 'open' })]

            // Render multiple times
            const allClasses: string[][] = []
            for (let i = 0; i < renderCount; i++) {
              const wrapper = mount(TaskListCard, {
                props: {
                  variant: 'assigned',
                  tasks,
                },
              })
              const priorityBadge = wrapper.find('span.rounded-md')
              allClasses.push(priorityBadge.classes())
            }

            // All renders should produce identical class lists
            const firstClassList = allClasses[0]
            allClasses.forEach(classList => {
              expect(classList).toEqual(firstClassList)
            })
          }
        )
      )
    })
  })
