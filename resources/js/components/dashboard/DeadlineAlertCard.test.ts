/**
 * DeadlineAlertCard Component Tests
 *
 * Tests the DeadlineAlertCard component for proper rendering with props,
 * urgency-to-color mapping, loading states, empty states, and accessibility.
 *
 * @see Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.7, 4.8, 14.1, 14.3
 */

import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import * as fc from 'fast-check'
import DeadlineAlertCard from './DeadlineAlertCard.vue'
import type { AlertType, DeadlineTask } from '@/types/dashboard'

/**
 * Helper to generate a random task for testing
 */
const generateTask = (id: number): DeadlineTask => ({
  id,
  title: `Task ${id}`,
  client: { name: `Client ${id}` },
  release_date: new Date(2024, 0, id + 1).toISOString(),
})

/**
 * Helper to generate an array of tasks
 */
const generateTasks = (count: number): DeadlineTask[] =>
  Array.from({ length: count }, (_, i) => generateTask(i + 1))

describe('DeadlineAlertCard', () => {
  describe('renders with all required props', () => {
    it('renders an article element', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 5,
          tasks: [],
        },
      })
      expect(wrapper.find('article').exists()).toBe(true)
    })

    it('renders the correct label for overdue type', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 5,
          tasks: [],
        },
      })
      expect(wrapper.text()).toContain('Task Overdue')
    })

    it('renders the correct label for due_soon type', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'due_soon',
          count: 3,
          tasks: [],
        },
      })
      expect(wrapper.text()).toContain('Task Due Soon (H-7)')
    })

    it('renders the count value', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 42,
          tasks: [],
        },
      })
      expect(wrapper.text()).toContain('42')
    })

    it('applies neo-brutalism base styles', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 5,
          tasks: [],
        },
      })
      const article = wrapper.find('article')
      expect(article.classes()).toContain('rounded-xl')
      expect(article.classes()).toContain('border-[1.5px]')
      expect(article.classes()).toContain('p-6')
    })

    it('applies shadow styles', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 5,
          tasks: [],
        },
      })
      const article = wrapper.find('article')
      // Shadow class should be present
      const hasShadow = article.classes().some(c => c.includes('shadow-['))
      expect(hasShadow).toBe(true)
    })

    it('has aria-label attribute on article element', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 5,
          tasks: [],
        },
      })
      expect(wrapper.find('article').attributes('aria-label')).toBeDefined()
    })
  })

  describe('overdue variant renders with red styling', () => {
    it('applies red border class for overdue type', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 5,
          tasks: [],
        },
      })
      const article = wrapper.find('article')
      expect(article.classes()).toContain('border-tm-danger')
    })

    it('applies red background class for overdue type', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 5,
          tasks: [],
        },
      })
      const article = wrapper.find('article')
      expect(article.classes()).toContain('bg-tm-danger-pale/60')
    })

    it('applies red text color for label in overdue card', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 5,
          tasks: [],
        },
      })
      const labelElement = wrapper.find('p.text-sm.font-medium')
      expect(labelElement.classes()).toContain('text-red-700')
    })

    it('applies red text color for count in overdue card', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 5,
          tasks: [],
        },
      })
      const countElement = wrapper.find('p.text-3xl.font-bold')
      expect(countElement.classes()).toContain('text-red-700')
    })

    it('applies red styling to icon container for overdue type', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 5,
          tasks: [],
        },
      })
      // Find icon container (rounded-xl with border)
      const iconContainers = wrapper.findAll('div.rounded-xl')
      const iconContainer = iconContainers.find(div => 
        div.classes().includes('border-[1.5px]') && 
        div.classes().includes('p-3')
      )
      expect(iconContainer).toBeDefined()
      expect(iconContainer!.classes()).toContain('bg-red-100')
      expect(iconContainer!.classes()).toContain('border-red-300')
    })

    it('applies red color to icon for overdue type', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 5,
          tasks: [],
        },
      })
      const icon = wrapper.find('svg.h-6.w-6')
      expect(icon.classes()).toContain('text-red-600')
    })

    it('applies red border to task items for overdue type', () => {
      const tasks = generateTasks(2)
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 2,
          tasks,
        },
      })
      const taskItems = wrapper.findAll('div.rounded-lg.border-\\[1\\.5px\\]')
      expect(taskItems.length).toBeGreaterThan(0)
      taskItems.forEach(item => {
        const hasRedBorder = item.classes().some(c => 
          c.includes('border-red-200') || c.includes('border-red-900')
        )
        expect(hasRedBorder).toBe(true)
      })
    })

    it('applies red color to release date for overdue type', () => {
      const tasks = generateTasks(1)
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 1,
          tasks,
        },
      })
      const dateElements = wrapper.findAll('span.font-semibold.flex-shrink-0')
      expect(dateElements.length).toBeGreaterThan(0)
      dateElements.forEach(el => {
        expect(el.classes()).toContain('text-red-600')
      })
    })
  })

  describe('due_soon variant renders with amber styling', () => {
    it('applies amber border class for due_soon type', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'due_soon',
          count: 3,
          tasks: [],
        },
      })
      const article = wrapper.find('article')
      expect(article.classes()).toContain('border-tm-warning')
    })

    it('applies amber background class for due_soon type', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'due_soon',
          count: 3,
          tasks: [],
        },
      })
      const article = wrapper.find('article')
      expect(article.classes()).toContain('bg-tm-warning-pale/60')
    })

    it('applies amber text color for label in due_soon card', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'due_soon',
          count: 3,
          tasks: [],
        },
      })
      const labelElement = wrapper.find('p.text-sm.font-medium')
      expect(labelElement.classes()).toContain('text-amber-700')
    })

    it('applies amber text color for count in due_soon card', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'due_soon',
          count: 3,
          tasks: [],
        },
      })
      const countElement = wrapper.find('p.text-3xl.font-bold')
      expect(countElement.classes()).toContain('text-amber-700')
    })

    it('applies amber styling to icon container for due_soon type', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'due_soon',
          count: 3,
          tasks: [],
        },
      })
      const iconContainers = wrapper.findAll('div.rounded-xl')
      const iconContainer = iconContainers.find(div => 
        div.classes().includes('border-[1.5px]') && 
        div.classes().includes('p-3')
      )
      expect(iconContainer).toBeDefined()
      expect(iconContainer!.classes()).toContain('bg-amber-100')
      expect(iconContainer!.classes()).toContain('border-amber-300')
    })

    it('applies amber color to icon for due_soon type', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'due_soon',
          count: 3,
          tasks: [],
        },
      })
      const icon = wrapper.find('svg.h-6.w-6')
      expect(icon.classes()).toContain('text-amber-600')
    })

    it('applies amber border to task items for due_soon type', () => {
      const tasks = generateTasks(2)
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'due_soon',
          count: 2,
          tasks,
        },
      })
      const taskItems = wrapper.findAll('div.rounded-lg.border-\\[1\\.5px\\]')
      expect(taskItems.length).toBeGreaterThan(0)
      taskItems.forEach(item => {
        const hasAmberBorder = item.classes().some(c => 
          c.includes('border-amber-200') || c.includes('border-amber-900')
        )
        expect(hasAmberBorder).toBe(true)
      })
    })

    it('applies amber color to release date for due_soon type', () => {
      const tasks = generateTasks(1)
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'due_soon',
          count: 1,
          tasks,
        },
      })
      const dateElements = wrapper.findAll('span.font-semibold.flex-shrink-0')
      expect(dateElements.length).toBeGreaterThan(0)
      dateElements.forEach(el => {
        expect(el.classes()).toContain('text-amber-600')
      })
    })
  })

  describe('empty state displays appropriate message', () => {
    it('displays empty state message for overdue when no tasks', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 0,
          tasks: [],
        },
      })
      expect(wrapper.text()).toContain('Tidak ada task overdue.')
    })

    it('displays empty state message for due_soon when no tasks', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'due_soon',
          count: 0,
          tasks: [],
        },
      })
      expect(wrapper.text()).toContain('Tidak ada task due soon.')
    })

    it('does not display empty state when tasks exist', () => {
      const tasks = generateTasks(1)
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 1,
          tasks,
        },
      })
      expect(wrapper.text()).not.toContain('Tidak ada task overdue.')
    })
  })

  describe('"Lihat Semua" link appears when tasks > 10', () => {
    it('displays "Lihat Semua" link when tasks exceed 10', () => {
      const tasks = generateTasks(15)
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 15,
          tasks,
          viewAllLink: '/tasks?filter=overdue',
        },
      })
      expect(wrapper.text()).toContain('Lihat Semua')
    })

    it('does not display "Lihat Semua" link when tasks <= 10', () => {
      const tasks = generateTasks(5)
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 5,
          tasks,
          viewAllLink: '/tasks?filter=overdue',
        },
      })
      expect(wrapper.text()).not.toContain('Lihat Semua')
    })

    it('does not display "Lihat Semua" link when viewAllLink is not provided', () => {
      const tasks = generateTasks(15)
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 15,
          tasks,
        },
      })
      expect(wrapper.text()).not.toContain('Lihat Semua')
    })

    it('links to the correct URL', () => {
      const tasks = generateTasks(15)
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 15,
          tasks,
          viewAllLink: '/tasks?filter=overdue',
        },
      })
      const link = wrapper.find('a')
      expect(link.exists()).toBe(true)
      expect(link.attributes('href')).toBe('/tasks?filter=overdue')
    })
  })

  describe('loading skeleton displays correctly', () => {
    it('displays loading skeleton when loading is true', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 5,
          tasks: [],
          loading: true,
        },
      })
      expect(wrapper.find('.animate-pulse').exists()).toBe(true)
    })

    it('hides content when loading is true', () => {
      const tasks = generateTasks(3)
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 3,
          tasks,
          loading: true,
        },
      })
      // Should not display label, count, or tasks
      expect(wrapper.text()).not.toContain('Task Overdue')
      expect(wrapper.text()).not.toContain('3')
    })

    it('shows content when loading is false (default)', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 5,
          tasks: [],
          loading: false,
        },
      })
      expect(wrapper.text()).toContain('Task Overdue')
      expect(wrapper.text()).toContain('5')
    })

    it('has skeleton placeholder elements', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 5,
          tasks: [],
          loading: true,
        },
      })
      const skeleton = wrapper.find('.animate-pulse')
      // Should have skeleton bars
      const skeletonBars = skeleton.findAll('div[class*="bg-muted"]')
      expect(skeletonBars.length).toBeGreaterThanOrEqual(2)
    })

    it('applies pulse animation to skeleton', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 5,
          tasks: [],
          loading: true,
        },
      })
      expect(wrapper.find('.animate-pulse').exists()).toBe(true)
    })
  })

  describe('task list rendering', () => {
    it('renders up to 10 tasks', () => {
      const tasks = generateTasks(15)
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 15,
          tasks,
        },
      })
      const taskItems = wrapper.findAll('div.rounded-lg.border-\\[1\\.5px\\]')
      expect(taskItems.length).toBe(10)
    })

    it('renders task title', () => {
      const tasks = generateTasks(1)
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 1,
          tasks,
        },
      })
      expect(wrapper.text()).toContain('Task 1')
    })

    it('renders client name', () => {
      const tasks = generateTasks(1)
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 1,
          tasks,
        },
      })
      expect(wrapper.text()).toContain('Client 1')
    })

    it('renders release date', () => {
      const tasks = generateTasks(1)
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 1,
          tasks,
        },
      })
      // Date should be formatted (Indonesian locale)
      expect(wrapper.text()).toContain('2/1/2024')
    })

    it('handles missing client name', () => {
      const tasks: DeadlineTask[] = [{ id: 1, title: 'Task 1', release_date: '2024-01-01' }]
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 1,
          tasks,
        },
      })
      expect(wrapper.text()).toContain('-')
    })

    it('handles missing release date', () => {
      const tasks: DeadlineTask[] = [{ id: 1, title: 'Task 1', client: { name: 'Client 1' } }]
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 1,
          tasks,
        },
      })
      expect(wrapper.text()).toContain('-')
    })
  })

  describe('ARIA label format', () => {
    it('has aria-label attribute on article element', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 5,
          tasks: [],
        },
      })
      expect(wrapper.find('article').attributes('aria-label')).toBeDefined()
    })

    it('formats aria-label as "{label}: {count} tasks" for overdue', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 5,
          tasks: [],
        },
      })
      expect(wrapper.find('article').attributes('aria-label')).toBe('Task Overdue: 5 tasks')
    })

    it('formats aria-label as "{label}: {count} tasks" for due_soon', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'due_soon',
          count: 3,
          tasks: [],
        },
      })
      expect(wrapper.find('article').attributes('aria-label')).toBe('Task Due Soon (H-7): 3 tasks')
    })

    it('handles zero count correctly', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 0,
          tasks: [],
        },
      })
      expect(wrapper.find('article').attributes('aria-label')).toBe('Task Overdue: 0 tasks')
    })

    it('handles large counts correctly', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 1000,
          tasks: [],
        },
      })
      expect(wrapper.find('article').attributes('aria-label')).toBe('Task Overdue: 1000 tasks')
    })
  })

  describe('hover classes are applied', () => {
    it('has transition classes', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 5,
          tasks: [],
        },
      })
      const article = wrapper.find('article')
      expect(article.classes()).toContain('transition-all')
      expect(article.classes()).toContain('duration-200')
      expect(article.classes()).toContain('ease-out')
    })

    it('has hover shadow class', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 5,
          tasks: [],
        },
      })
      const article = wrapper.find('article')
      const hasHoverShadow = article.classes().some(c => c.includes('hover:shadow-['))
      expect(hasHoverShadow).toBe(true)
    })

    it('has hover translate class', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 5,
          tasks: [],
        },
      })
      const article = wrapper.find('article')
      expect(article.classes()).toContain('hover:-translate-y-0.5')
    })
  })

  describe('semantic HTML structure', () => {
    it('uses article element for semantic structure', () => {
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 5,
          tasks: [],
        },
      })
      expect(wrapper.find('article').exists()).toBe(true)
    })

    it('has proper text hierarchy with paragraph elements', () => {
      const tasks = generateTasks(2)
      const wrapper = mount(DeadlineAlertCard, {
        props: {
          type: 'overdue',
          count: 2,
          tasks,
        },
      })
      const paragraphs = wrapper.findAll('p')
      expect(paragraphs.length).toBeGreaterThanOrEqual(2) // Label and count
    })
  })

  /**
   * Property-based tests for Urgency-to-Color Mapping
   *
   * **Validates: Requirements 4.1, 4.2, 14.3**
   * Property 4: Urgency-to-Color Mapping - verifies all color elements use correct urgency color
   */
  describe('Property: Urgency-to-Color Mapping', () => {
    it('always applies correct red color elements for overdue type', () => {
      fc.assert(
        fc.property(
          fc.integer({ min: 0, max: 100 }),
          fc.integer({ min: 1, max: 15 }),
          (count, taskCount) => {
            const tasks = generateTasks(taskCount)
            const wrapper = mount(DeadlineAlertCard, {
              props: {
                type: 'overdue',
                count,
                tasks,
              },
            })

            const article = wrapper.find('article')

            // Verify border uses red (tm-danger)
            const hasRedBorder = article.classes().some(c => 
              c === 'border-tm-danger' || c.includes('border-red')
            )
            if (!hasRedBorder) return false

            // Verify background uses red (tm-danger-pale)
            const hasRedBackground = article.classes().some(c => 
              c === 'bg-tm-danger-pale/60' || c.includes('bg-tm-danger')
            )
            if (!hasRedBackground) return false

            // Verify label text uses red
            const labelElement = wrapper.find('p.text-sm.font-medium')
            const hasRedLabelText = labelElement.classes().some(c => 
              c.includes('text-red-')
            )
            if (!hasRedLabelText) return false

            // Verify count text uses red
            const countElement = wrapper.find('p.text-3xl.font-bold')
            const hasRedCountText = countElement.classes().some(c => 
              c.includes('text-red-')
            )
            if (!hasRedCountText) return false

            // Verify icon container uses red (need to check bg and border separately)
            const iconContainers = wrapper.findAll('div.rounded-xl.p-3')
            const hasRedIconContainer = iconContainers.some(container => {
              const classes = container.classes()
              const hasRedBg = classes.some(c => c.includes('bg-red-'))
              const hasRedBorder = classes.some(c => c.includes('border-red-'))
              return hasRedBg && hasRedBorder
            })
            if (!hasRedIconContainer) return false

            // Verify icon uses red color
            const icon = wrapper.find('svg.h-6.w-6')
            const hasRedIcon = icon.classes().some(c => c.includes('text-red-'))
            if (!hasRedIcon) return false

            return true
          }
        )
      )
    })

    it('always applies correct amber color elements for due_soon type', () => {
      fc.assert(
        fc.property(
          fc.integer({ min: 0, max: 100 }),
          fc.integer({ min: 1, max: 15 }),
          (count, taskCount) => {
            const tasks = generateTasks(taskCount)
            const wrapper = mount(DeadlineAlertCard, {
              props: {
                type: 'due_soon',
                count,
                tasks,
              },
            })

            const article = wrapper.find('article')

            // Verify border uses amber (tm-warning)
            const hasAmberBorder = article.classes().some(c => 
              c === 'border-tm-warning' || c.includes('border-amber')
            )
            if (!hasAmberBorder) return false

            // Verify background uses amber (tm-warning-pale)
            const hasAmberBackground = article.classes().some(c => 
              c === 'bg-tm-warning-pale/60' || c.includes('bg-tm-warning')
            )
            if (!hasAmberBackground) return false

            // Verify label text uses amber
            const labelElement = wrapper.find('p.text-sm.font-medium')
            const hasAmberLabelText = labelElement.classes().some(c => 
              c.includes('text-amber-')
            )
            if (!hasAmberLabelText) return false

            // Verify count text uses amber
            const countElement = wrapper.find('p.text-3xl.font-bold')
            const hasAmberCountText = countElement.classes().some(c => 
              c.includes('text-amber-')
            )
            if (!hasAmberCountText) return false

            // Verify icon container uses amber (need to check bg and border separately)
            const iconContainers = wrapper.findAll('div.rounded-xl.p-3')
            const hasAmberIconContainer = iconContainers.some(container => {
              const classes = container.classes()
              const hasAmberBg = classes.some(c => c.includes('bg-amber-'))
              const hasAmberBorder = classes.some(c => c.includes('border-amber-'))
              return hasAmberBg && hasAmberBorder
            })
            if (!hasAmberIconContainer) return false

            // Verify icon uses amber color
            const icon = wrapper.find('svg.h-6.w-6')
            const hasAmberIcon = icon.classes().some(c => c.includes('text-amber-'))
            if (!hasAmberIcon) return false

            return true
          }
        )
      )
    })

    it('always applies correct task item border colors based on type', () => {
      fc.assert(
        fc.property(
          fc.constantFrom<AlertType>('overdue', 'due_soon'),
          fc.integer({ min: 1, max: 15 }),
          (type, taskCount) => {
            const tasks = generateTasks(taskCount)
            const wrapper = mount(DeadlineAlertCard, {
              props: {
                type,
                count: taskCount,
                tasks,
              },
            })

            const taskItems = wrapper.findAll('div.rounded-lg.border-\\[1\\.5px\\]')
            
            // Only check if we have task items (not in loading state)
            if (taskItems.length === 0) return true

            const expectedColor = type === 'overdue' ? 'red' : 'amber'
            
            const allHaveCorrectColor = taskItems.every(item => {
              const classes = item.classes()
              return classes.some(c => c.includes(`border-${expectedColor}`))
            })

            return allHaveCorrectColor
          }
        )
      )
    })

    it('always applies correct release date color based on type', () => {
      fc.assert(
        fc.property(
          fc.constantFrom<AlertType>('overdue', 'due_soon'),
          fc.integer({ min: 1, max: 10 }),
          (type, taskCount) => {
            const tasks = generateTasks(taskCount)
            const wrapper = mount(DeadlineAlertCard, {
              props: {
                type,
                count: taskCount,
                tasks,
              },
            })

            const dateElements = wrapper.findAll('span.font-semibold.flex-shrink-0')
            
            if (dateElements.length === 0) return true

            const expectedColor = type === 'overdue' ? 'red' : 'amber'
            
            const allHaveCorrectColor = dateElements.every(el => {
              const classes = el.classes()
              return classes.some(c => c.includes(`text-${expectedColor}-`))
            })

            return allHaveCorrectColor
          }
        )
      )
    })

    it('always uses Trustmedis danger color for overdue border', () => {
      fc.assert(
        fc.property(
          fc.integer({ min: 0, max: 100 }),
          (count) => {
            const wrapper = mount(DeadlineAlertCard, {
              props: {
                type: 'overdue',
                count,
                tasks: [],
              },
            })

            const article = wrapper.find('article')
            return article.classes().includes('border-tm-danger')
          }
        )
      )
    })

    it('always uses Trustmedis warning color for due_soon border', () => {
      fc.assert(
        fc.property(
          fc.integer({ min: 0, max: 100 }),
          (count) => {
            const wrapper = mount(DeadlineAlertCard, {
              props: {
                type: 'due_soon',
                count,
                tasks: [],
              },
            })

            const article = wrapper.find('article')
            return article.classes().includes('border-tm-warning')
          }
        )
      )
    })

    it('always applies urgency colors consistently across all color elements for overdue type', () => {
      fc.assert(
        fc.property(
          fc.integer({ min: 1, max: 50 }),
          fc.integer({ min: 1, max: 10 }),
          (count, taskCount) => {
            const tasks = generateTasks(taskCount)
            const wrapper = mount(DeadlineAlertCard, {
              props: {
                type: 'overdue',
                count,
                tasks,
              },
            })

            // All color elements should use red-based colors
            const article = wrapper.find('article')
            
            // Card border
            expect(article.classes()).toContain('border-tm-danger')
            
            // Card background
            expect(article.classes().some(c => c.includes('bg-tm-danger'))).toBe(true)
            
            // Label text
            const labelElement = wrapper.find('p.text-sm.font-medium')
            expect(labelElement.classes().some(c => c.includes('text-red-'))).toBe(true)
            
            // Count text
            const countElement = wrapper.find('p.text-3xl.font-bold')
            expect(countElement.classes().some(c => c.includes('text-red-'))).toBe(true)
            
            // Icon container
            const iconContainers = wrapper.findAll('div.rounded-xl.p-3')
            const iconContainer = iconContainers.find(c => c.classes().includes('border-[1.5px]'))
            expect(iconContainer).toBeDefined()
            expect(iconContainer!.classes().some(c => c.includes('bg-red-'))).toBe(true)
            expect(iconContainer!.classes().some(c => c.includes('border-red-'))).toBe(true)
            
            // Icon
            const icon = wrapper.find('svg.h-6.w-6')
            expect(icon.classes().some(c => c.includes('text-red-'))).toBe(true)
            
            // Task items
            const taskItems = wrapper.findAll('div.rounded-lg.border-\\[1\\.5px\\]')
            taskItems.forEach(item => {
              expect(item.classes().some(c => c.includes('border-red-'))).toBe(true)
            })
            
            // Date elements
            const dateElements = wrapper.findAll('span.font-semibold.flex-shrink-0')
            dateElements.forEach(el => {
              expect(el.classes().some(c => c.includes('text-red-'))).toBe(true)
            })

            return true
          }
        )
      )
    })

    it('always applies urgency colors consistently across all color elements for due_soon type', () => {
      fc.assert(
        fc.property(
          fc.integer({ min: 1, max: 50 }),
          fc.integer({ min: 1, max: 10 }),
          (count, taskCount) => {
            const tasks = generateTasks(taskCount)
            const wrapper = mount(DeadlineAlertCard, {
              props: {
                type: 'due_soon',
                count,
                tasks,
              },
            })

            // All color elements should use amber-based colors
            const article = wrapper.find('article')
            
            // Card border
            expect(article.classes()).toContain('border-tm-warning')
            
            // Card background
            expect(article.classes().some(c => c.includes('bg-tm-warning'))).toBe(true)
            
            // Label text
            const labelElement = wrapper.find('p.text-sm.font-medium')
            expect(labelElement.classes().some(c => c.includes('text-amber-'))).toBe(true)
            
            // Count text
            const countElement = wrapper.find('p.text-3xl.font-bold')
            expect(countElement.classes().some(c => c.includes('text-amber-'))).toBe(true)
            
            // Icon container
            const iconContainers = wrapper.findAll('div.rounded-xl.p-3')
            const iconContainer = iconContainers.find(c => c.classes().includes('border-[1.5px]'))
            expect(iconContainer).toBeDefined()
            expect(iconContainer!.classes().some(c => c.includes('bg-amber-'))).toBe(true)
            expect(iconContainer!.classes().some(c => c.includes('border-amber-'))).toBe(true)
            
            // Icon
            const icon = wrapper.find('svg.h-6.w-6')
            expect(icon.classes().some(c => c.includes('text-amber-'))).toBe(true)
            
            // Task items
            const taskItems = wrapper.findAll('div.rounded-lg.border-\\[1\\.5px\\]')
            taskItems.forEach(item => {
              expect(item.classes().some(c => c.includes('border-amber-'))).toBe(true)
            })
            
            // Date elements
            const dateElements = wrapper.findAll('span.font-semibold.flex-shrink-0')
            dateElements.forEach(el => {
              expect(el.classes().some(c => c.includes('text-amber-'))).toBe(true)
            })

            return true
          }
        )
      )
    })
  })
})

  /**
   * Property-based tests for Task List Rendering
   *
   * **Validates: Requirements 4.3, 4.4**
   * Property 7: Task List Rendering - verifies each task's title, client name, and release date is present in rendered output
   */
  describe('Property: Task List Rendering', () => {
    /**
     * Generator for ISO date strings
     * Generates valid dates between 2020 and 2030 as ISO strings
     */
    const dateStringArbitrary = fc.tuple(
      fc.integer({ min: 2020, max: 2030 }), // year
      fc.integer({ min: 1, max: 12 }), // month
      fc.integer({ min: 1, max: 28 }) // day (use 28 to avoid invalid dates)
    ).map(([year, month, day]) => {
      const date = new Date(year, month - 1, day);
      return date.toISOString();
    });

    /**
     * Smart generator for DeadlineTask
     * Generates realistic task data with constrained string values
     * Note: Titles are trimmed to match Vue's template whitespace handling
     */
    const deadlineTaskArbitrary = fc.record({
      id: fc.integer({ min: 1, max: 10000 }),
      title: fc.string({ minLength: 1, maxLength: 100 }).map(s => s.trim()).filter(s => s.length > 0),
      client: fc.option(
        fc.record({
          name: fc.string({ minLength: 1, maxLength: 50 }).map(s => s.trim()).filter(s => s.length > 0),
        }),
        { freq: 90, nil: undefined } // 90% chance of having a client
      ),
      release_date: fc.option(
        dateStringArbitrary,
        { freq: 95, nil: undefined } // 95% chance of having a release date
      ),
    });

    /**
     * Generator for arrays of tasks (0-10 items)
     * This matches the task requirement to test with 0-10 items
     */
    const taskArrayArbitrary = fc.array(deadlineTaskArbitrary, { minLength: 0, maxLength: 10 });

    it('always renders each task title in the output for random task arrays (0-10 items)', () => {
      fc.assert(
        fc.property(
          fc.constantFrom<AlertType>('overdue', 'due_soon'),
          taskArrayArbitrary,
          (type, tasks) => {
            const wrapper = mount(DeadlineAlertCard, {
              props: {
                type,
                count: tasks.length,
                tasks,
              },
            });

            const renderedText = wrapper.text();

            // Verify each task's title is present in the rendered output
            for (const task of tasks) {
              expect(renderedText).toContain(task.title);
            }

            return true;
          }
        )
      );
    });

    it('always renders each task client name in the output when present', () => {
      fc.assert(
        fc.property(
          fc.constantFrom<AlertType>('overdue', 'due_soon'),
          taskArrayArbitrary,
          (type, tasks) => {
            const wrapper = mount(DeadlineAlertCard, {
              props: {
                type,
                count: tasks.length,
                tasks,
              },
            });

            const renderedText = wrapper.text();

            // Verify each task's client name is present in the rendered output (when client exists)
            for (const task of tasks) {
              if (task.client?.name) {
                expect(renderedText).toContain(task.client.name);
              }
            }

            return true;
          }
        )
      );
    });

    it('always renders each task release date in the output when present', () => {
      fc.assert(
        fc.property(
          fc.constantFrom<AlertType>('overdue', 'due_soon'),
          taskArrayArbitrary,
          (type, tasks) => {
            const wrapper = mount(DeadlineAlertCard, {
              props: {
                type,
                count: tasks.length,
                tasks,
              },
            });

            const renderedText = wrapper.text();

            // Verify each task's release date is present in the rendered output (when date exists)
            for (const task of tasks) {
              if (task.release_date) {
                // The component formats dates using Indonesian locale
                const formattedDate = new Date(task.release_date).toLocaleDateString('id-ID');
                expect(renderedText).toContain(formattedDate);
              }
            }

            return true;
          }
        )
      );
    });

    it('always renders all task data (title, client, date) for each task in the array', () => {
      fc.assert(
        fc.property(
          fc.constantFrom<AlertType>('overdue', 'due_soon'),
          taskArrayArbitrary,
          (type, tasks) => {
            const wrapper = mount(DeadlineAlertCard, {
              props: {
                type,
                count: tasks.length,
                tasks,
              },
            });

            const renderedText = wrapper.text();

            // Verify all three pieces of data are present for each task
            for (const task of tasks) {
              // Title must always be present
              expect(renderedText).toContain(task.title);

              // Client name must be present when defined, otherwise "-" should appear
              if (task.client?.name) {
                expect(renderedText).toContain(task.client.name);
              }

              // Release date must be present when defined, otherwise "-" should appear
              if (task.release_date) {
                const formattedDate = new Date(task.release_date).toLocaleDateString('id-ID');
                expect(renderedText).toContain(formattedDate);
              }
            }

            return true;
          }
        )
      );
    });

    it('renders "-" for missing client names', () => {
      fc.assert(
        fc.property(
          fc.constantFrom<AlertType>('overdue', 'due_soon'),
          // Generate tasks without clients
          fc.array(
            fc.record({
              id: fc.integer({ min: 1, max: 10000 }),
              title: fc.string({ minLength: 1, maxLength: 50 }).map(s => s.trim()).filter(s => s.length > 0),
              client: fc.constant(undefined),
              release_date: fc.option(
                dateStringArbitrary,
                { freq: 50, nil: undefined }
              ),
            }),
            { minLength: 1, maxLength: 5 }
          ),
          (type, tasks) => {
            const wrapper = mount(DeadlineAlertCard, {
              props: {
                type,
                count: tasks.length,
                tasks,
              },
            });

            const renderedText = wrapper.text();

            // All tasks without clients should show "-"
            const dashCount = (renderedText.match(/-/g) || []).length;
            const tasksWithoutClient = tasks.filter(t => !t.client);
            const tasksWithoutDate = tasks.filter(t => !t.release_date);

            // Each task without client or date contributes a "-"
            expect(dashCount).toBeGreaterThanOrEqual(tasksWithoutClient.length + tasksWithoutDate.length);

            return true;
          }
        )
      );
    });

    it('renders "-" for missing release dates', () => {
      fc.assert(
        fc.property(
          fc.constantFrom<AlertType>('overdue', 'due_soon'),
          // Generate tasks without release dates
          fc.array(
            fc.record({
              id: fc.integer({ min: 1, max: 10000 }),
              title: fc.string({ minLength: 1, maxLength: 50 }).map(s => s.trim()).filter(s => s.length > 0),
              client: fc.option(
                fc.record({
                  name: fc.string({ minLength: 1, maxLength: 30 }).map(s => s.trim()).filter(s => s.length > 0),
                }),
                { freq: 50, nil: undefined }
              ),
              release_date: fc.constant(undefined),
            }),
            { minLength: 1, maxLength: 5 }
          ),
          (type, tasks) => {
            const wrapper = mount(DeadlineAlertCard, {
              props: {
                type,
                count: tasks.length,
                tasks,
              },
            });

            const renderedText = wrapper.text();

            // All tasks without release dates should show "-"
            const dashCount = (renderedText.match(/-/g) || []).length;
            const tasksWithoutDate = tasks.filter(t => !t.release_date);

            expect(dashCount).toBeGreaterThanOrEqual(tasksWithoutDate.length);

            return true;
          }
        )
      );
    });

    it('renders exactly the number of task items as the input array length (up to 10)', () => {
      fc.assert(
        fc.property(
          fc.constantFrom<AlertType>('overdue', 'due_soon'),
          fc.integer({ min: 0, max: 10 }),
          (type, taskCount) => {
            const tasks = Array.from({ length: taskCount }, (_, i) => ({
              id: i + 1,
              title: `Task ${i + 1}`,
              client: { name: `Client ${i + 1}` },
              release_date: new Date(2024, 0, i + 1).toISOString(),
            }));

            const wrapper = mount(DeadlineAlertCard, {
              props: {
                type,
                count: taskCount,
                tasks,
              },
            });

            // Count task item divs
            const taskItems = wrapper.findAll('div.rounded-lg.border-\\[1\\.5px\\]');
            expect(taskItems.length).toBe(taskCount);

            return true;
          }
        )
      );
    });

    it('handles empty task array correctly', () => {
      fc.assert(
        fc.property(
          fc.constantFrom<AlertType>('overdue', 'due_soon'),
          (type) => {
            const wrapper = mount(DeadlineAlertCard, {
              props: {
                type,
                count: 0,
                tasks: [],
              },
            });

            // Should show empty state message
            const emptyMessages = {
              overdue: 'Tidak ada task overdue.',
              due_soon: 'Tidak ada task due soon.',
            };
            expect(wrapper.text()).toContain(emptyMessages[type]);

            // Should not have any task items
            const taskItems = wrapper.findAll('div.rounded-lg.border-\\[1\\.5px\\]');
            expect(taskItems.length).toBe(0);

            return true;
          }
        )
      );
    });

    it('always renders task data with correct visual structure regardless of content', () => {
      fc.assert(
        fc.property(
          fc.constantFrom<AlertType>('overdue', 'due_soon'),
          taskArrayArbitrary,
          (type, tasks) => {
            const wrapper = mount(DeadlineAlertCard, {
              props: {
                type,
                count: tasks.length,
                tasks,
              },
            });

            // Each task item should have:
            // 1. A title (in a paragraph with class "truncate")
            // 2. Client name and release date in a flex container

            const taskItems = wrapper.findAll('div.rounded-lg.border-\\[1\\.5px\\]');

            expect(taskItems.length).toBe(tasks.length);

            taskItems.forEach((item, index) => {
              const task = tasks[index];

              // Title should be in a paragraph with truncate class
              const titleEl = item.find('p.text-sm.font-semibold.truncate');
              expect(titleEl.exists()).toBe(true);
              // Use toContain instead of toBe since Vue may normalize whitespace
              expect(titleEl.text()).toContain(task.title);

              // Client name and date should be in a flex container
              const metaContainer = item.find('div.flex.items-center.justify-between');
              expect(metaContainer.exists()).toBe(true);
            });

            return true;
          }
        )
      );
    });
  });

  /**
   * Property-based tests for "Lihat Semua" Link Display
   *
   * **Validates: Requirements 4.7, 4.8**
   * Property 8: "Lihat Semua" Link Display - verifies link appears only when length > 10
   */
  describe('Property: "Lihat Semua" Link Display', () => {
    /**
     * Generator for task arrays of varying lengths (0-20 items)
     * We test a range that includes boundary values around 10
     */
    const taskArrayArbitrary = fc.integer({ min: 0, max: 20 }).map(count => {
      return Array.from({ length: count }, (_, i) => ({
        id: i + 1,
        title: `Task ${i + 1}`,
        client: { name: `Client ${i + 1}` },
        release_date: new Date(2024, 0, (i % 28) + 1).toISOString(),
      }));
    });

    /**
     * Generator for optional viewAllLink
     * 80% chance of having a link, 20% chance of undefined
     */
    const viewAllLinkArbitrary = fc.option(
      fc.webUrl().map(url => url + '/tasks?filter=overdue'),
      { freq: 80, nil: undefined }
    );

    it('always displays "Lihat Semua" link only when tasks length > 10 and viewAllLink is provided', () => {
      fc.assert(
        fc.property(
          fc.constantFrom<AlertType>('overdue', 'due_soon'),
          taskArrayArbitrary,
          viewAllLinkArbitrary,
          (type, tasks, viewAllLink) => {
            const wrapper = mount(DeadlineAlertCard, {
              props: {
                type,
                count: tasks.length,
                tasks,
                viewAllLink,
              },
            });

            const renderedText = wrapper.text();
            const hasLihatSemua = renderedText.includes('Lihat Semua');

            // Link should appear ONLY when:
            // 1. tasks.length > 10 AND
            // 2. viewAllLink is provided
            const shouldShowLink = tasks.length > 10 && viewAllLink !== undefined;

            expect(hasLihatSemua).toBe(shouldShowLink);

            return true;
          }
        )
      );
    });

    it('never displays "Lihat Semua" link when tasks length <= 10', () => {
      fc.assert(
        fc.property(
          fc.constantFrom<AlertType>('overdue', 'due_soon'),
          fc.integer({ min: 0, max: 10 }),
          viewAllLinkArbitrary,
          (type, taskCount, viewAllLink) => {
            const tasks = Array.from({ length: taskCount }, (_, i) => ({
              id: i + 1,
              title: `Task ${i + 1}`,
              client: { name: `Client ${i + 1}` },
              release_date: new Date(2024, 0, (i % 28) + 1).toISOString(),
            }));

            const wrapper = mount(DeadlineAlertCard, {
              props: {
                type,
                count: taskCount,
                tasks,
                viewAllLink,
              },
            });

            // When tasks <= 10, "Lihat Semua" should NEVER appear
            expect(wrapper.text()).not.toContain('Lihat Semua');

            return true;
          }
        )
      );
    });

    it('never displays "Lihat Semua" link when viewAllLink is not provided', () => {
      fc.assert(
        fc.property(
          fc.constantFrom<AlertType>('overdue', 'due_soon'),
          fc.integer({ min: 11, max: 20 }),
          (type, taskCount) => {
            const tasks = Array.from({ length: taskCount }, (_, i) => ({
              id: i + 1,
              title: `Task ${i + 1}`,
              client: { name: `Client ${i + 1}` },
              release_date: new Date(2024, 0, (i % 28) + 1).toISOString(),
            }));

            const wrapper = mount(DeadlineAlertCard, {
              props: {
                type,
                count: taskCount,
                tasks,
                // viewAllLink is NOT provided
              },
            });

            // Even with tasks > 10, "Lihat Semua" should NOT appear without viewAllLink
            expect(wrapper.text()).not.toContain('Lihat Semua');

            return true;
          }
        )
      );
    });

    it('always displays "Lihat Semua" link when tasks length > 10 and viewAllLink is provided', () => {
      fc.assert(
        fc.property(
          fc.constantFrom<AlertType>('overdue', 'due_soon'),
          fc.integer({ min: 11, max: 20 }),
          fc.webUrl(),
          (type, taskCount, baseUrl) => {
            const tasks = Array.from({ length: taskCount }, (_, i) => ({
              id: i + 1,
              title: `Task ${i + 1}`,
              client: { name: `Client ${i + 1}` },
              release_date: new Date(2024, 0, (i % 28) + 1).toISOString(),
            }));

            const viewAllLink = `${baseUrl}/tasks?filter=${type}`;

            const wrapper = mount(DeadlineAlertCard, {
              props: {
                type,
                count: taskCount,
                tasks,
                viewAllLink,
              },
            });

            // When tasks > 10 AND viewAllLink is provided, "Lihat Semua" MUST appear
            expect(wrapper.text()).toContain('Lihat Semua');

            return true;
          }
        )
      );
    });

    it('always renders "Lihat Semua" as a link element when conditions are met', () => {
      fc.assert(
        fc.property(
          fc.constantFrom<AlertType>('overdue', 'due_soon'),
          fc.integer({ min: 11, max: 20 }),
          // Use realistic relative URLs (no special characters that get normalized)
          fc.oneof(
            fc.constant('/tasks?filter=overdue'),
            fc.constant('/tasks?filter=due_soon'),
            fc.constant('/admin/tasks'),
            fc.constant('/dashboard/overdue-tasks')
          ),
          (type, taskCount, viewAllLink) => {
            const tasks = Array.from({ length: taskCount }, (_, i) => ({
              id: i + 1,
              title: `Task ${i + 1}`,
              client: { name: `Client ${i + 1}` },
              release_date: new Date(2024, 0, (i % 28) + 1).toISOString(),
            }));

            const wrapper = mount(DeadlineAlertCard, {
              props: {
                type,
                count: taskCount,
                tasks,
                viewAllLink,
              },
            });

            // Find the link element
            const link = wrapper.find('a');

            // Link must exist
            expect(link.exists()).toBe(true);

            // Link must have correct href
            expect(link.attributes('href')).toBe(viewAllLink);

            // Link text must be "Lihat Semua"
            expect(link.text()).toContain('Lihat Semua');

            return true;
          }
        )
      );
    });

    it('always displays exactly 10 task items even when total tasks > 10', () => {
      fc.assert(
        fc.property(
          fc.constantFrom<AlertType>('overdue', 'due_soon'),
          fc.integer({ min: 11, max: 20 }),
          (type, taskCount) => {
            const tasks = Array.from({ length: taskCount }, (_, i) => ({
              id: i + 1,
              title: `Task ${i + 1}`,
              client: { name: `Client ${i + 1}` },
              release_date: new Date(2024, 0, (i % 28) + 1).toISOString(),
            }));

            const wrapper = mount(DeadlineAlertCard, {
              props: {
                type,
                count: taskCount,
                tasks,
                viewAllLink: '/tasks?filter=' + type,
              },
            });

            // Should render exactly 10 task items
            const taskItems = wrapper.findAll('div.rounded-lg.border-\\[1\\.5px\\]');
            expect(taskItems.length).toBe(10);

            return true;
          }
        )
      );
    });

    it('correctly handles the boundary condition at exactly 10 tasks', () => {
      fc.assert(
        fc.property(
          fc.constantFrom<AlertType>('overdue', 'due_soon'),
          viewAllLinkArbitrary,
          (type, viewAllLink) => {
            const tasks = Array.from({ length: 10 }, (_, i) => ({
              id: i + 1,
              title: `Task ${i + 1}`,
              client: { name: `Client ${i + 1}` },
              release_date: new Date(2024, 0, (i % 28) + 1).toISOString(),
            }));

            const wrapper = mount(DeadlineAlertCard, {
              props: {
                type,
                count: 10,
                tasks,
                viewAllLink,
              },
            });

            // At exactly 10 tasks, "Lihat Semua" should NOT appear
            expect(wrapper.text()).not.toContain('Lihat Semua');

            // Should render all 10 task items
            const taskItems = wrapper.findAll('div.rounded-lg.border-\\[1\\.5px\\]');
            expect(taskItems.length).toBe(10);

            return true;
          }
        )
      );
    });

    it('correctly handles the boundary condition at exactly 11 tasks', () => {
      fc.assert(
        fc.property(
          fc.constantFrom<AlertType>('overdue', 'due_soon'),
          fc.webUrl(),
          (type, baseUrl) => {
            const tasks = Array.from({ length: 11 }, (_, i) => ({
              id: i + 1,
              title: `Task ${i + 1}`,
              client: { name: `Client ${i + 1}` },
              release_date: new Date(2024, 0, (i % 28) + 1).toISOString(),
            }));

            const viewAllLink = `${baseUrl}/tasks?filter=${type}`;

            const wrapper = mount(DeadlineAlertCard, {
              props: {
                type,
                count: 11,
                tasks,
                viewAllLink,
              },
            });

            // At exactly 11 tasks, "Lihat Semua" SHOULD appear
            expect(wrapper.text()).toContain('Lihat Semua');

            // Should render only 10 task items (max display)
            const taskItems = wrapper.findAll('div.rounded-lg.border-\\[1\\.5px\\]');
            expect(taskItems.length).toBe(10);

            return true;
          }
        )
      );
    });

    it('maintains consistent behavior across both overdue and due_soon alert types', () => {
      fc.assert(
        fc.property(
          fc.integer({ min: 0, max: 20 }),
          viewAllLinkArbitrary,
          (taskCount, viewAllLink) => {
            const tasks = Array.from({ length: taskCount }, (_, i) => ({
              id: i + 1,
              title: `Task ${i + 1}`,
              client: { name: `Client ${i + 1}` },
              release_date: new Date(2024, 0, (i % 28) + 1).toISOString(),
            }));

            // Test both types with the same data
            const overdueWrapper = mount(DeadlineAlertCard, {
              props: {
                type: 'overdue',
                count: taskCount,
                tasks,
                viewAllLink,
              },
            });

            const dueSoonWrapper = mount(DeadlineAlertCard, {
              props: {
                type: 'due_soon',
                count: taskCount,
                tasks,
                viewAllLink,
              },
            });

            const shouldShowLink = taskCount > 10 && viewAllLink !== undefined;

            // Both types should have consistent "Lihat Semua" visibility
            expect(overdueWrapper.text().includes('Lihat Semua')).toBe(shouldShowLink);
            expect(dueSoonWrapper.text().includes('Lihat Semua')).toBe(shouldShowLink);

            return true;
          }
        )
      );
    });

    it('uses the correct viewAllLink URL when link is displayed', () => {
      fc.assert(
        fc.property(
          fc.constantFrom<AlertType>('overdue', 'due_soon'),
          fc.integer({ min: 11, max: 20 }),
          // Use realistic relative URL paths that don't get URL-encoded
          fc.oneof(
            fc.constant('/tasks?status=overdue'),
            fc.constant('/dashboard/tasks?filter=overdue'),
            fc.constant('/admin/tasks/overdue'),
            fc.constant('/tasks/due-soon'),
            fc.constant('/tasks?tab=overdue&page=1')
          ),
          (type, taskCount, viewAllLink) => {
            const tasks = Array.from({ length: taskCount }, (_, i) => ({
              id: i + 1,
              title: `Task ${i + 1}`,
              client: { name: `Client ${i + 1}` },
              release_date: new Date(2024, 0, (i % 28) + 1).toISOString(),
            }));

            const wrapper = mount(DeadlineAlertCard, {
              props: {
                type,
                count: taskCount,
                tasks,
                viewAllLink,
              },
            });

            const link = wrapper.find('a');

            if (link.exists()) {
              expect(link.attributes('href')).toBe(viewAllLink);
            }

            return true;
          }
        )
      );
    });
  });
