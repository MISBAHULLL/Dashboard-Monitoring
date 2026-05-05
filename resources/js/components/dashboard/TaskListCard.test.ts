/**
 * TaskListCard Component Unit Tests
 *
 * Tests the TaskListCard component for proper rendering with props,
 * variant-specific columns, status/priority badge color mapping, loading states,
 * empty states, and task filtering for 'assigned' variant.
 *
 * Requirements: 7.2, 7.3, 9.2, 9.3, 9.4
 */

import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
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

describe('TaskListCard', () => {
  describe('renders with all required props', () => {
    it('renders an article element', () => {
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks: [],
        },
      })
      expect(wrapper.find('article').exists()).toBe(true)
    })

    it('applies neo-brutalism base styles', () => {
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks: [],
        },
      })
      const article = wrapper.find('article')
      expect(article.classes()).toContain('rounded-xl')
      expect(article.classes()).toContain('border-[1.5px]')
      expect(article.classes()).toContain('border-border')
      expect(article.classes()).toContain('bg-card')
    })

    it('applies shadow styles', () => {
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks: [],
        },
      })
      const article = wrapper.find('article')
      const hasShadow = article.classes().some(c => c.includes('shadow-['))
      expect(hasShadow).toBe(true)
    })

    it('has transition and hover classes', () => {
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks: [],
        },
      })
      const article = wrapper.find('article')
      expect(article.classes()).toContain('transition-all')
      expect(article.classes()).toContain('duration-200')
      expect(article.classes()).toContain('ease-out')
      const hasHoverShadow = article.classes().some(c => c.includes('hover:shadow-['))
      expect(hasHoverShadow).toBe(true)
    })

    it('uses semantic table structure', () => {
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks: [],
        },
      })
      expect(wrapper.find('table').exists()).toBe(true)
      expect(wrapper.find('thead').exists()).toBe(true)
      expect(wrapper.find('tbody').exists()).toBe(true)
    })
  })

  describe("'recent' variant renders correct columns", () => {
    it('displays "5 Task Terbaru" title', () => {
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks: [],
        },
      })
      expect(wrapper.text()).toContain('5 Task Terbaru')
    })

    it('displays description for recent variant', () => {
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks: [],
        },
      })
      expect(wrapper.text()).toContain('Task yang baru saja dibuat ke dalam sistem.')
    })

    it('renders all expected column headers for recent variant', () => {
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks: [],
        },
      })
      const headers = wrapper.findAll('th')
      const headerTexts = headers.map(h => h.text())

      expect(headerTexts).toContain('Faskes / Client')
      expect(headerTexts).toContain('Judul Task')
      expect(headerTexts).toContain('Modul')
      expect(headerTexts).toContain('Status')
      expect(headerTexts).toContain('Tanggal Dibuat')
    })

    it('renders 5 column headers for recent variant', () => {
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks: [],
        },
      })
      const headers = wrapper.findAll('th')
      expect(headers.length).toBe(5)
    })

    it('renders task data in correct columns for recent variant', () => {
      const tasks = generateTasks(1, {
        title: 'Implement Feature',
        modul: 'Authentication',
        client: { id: 1, name: 'Hospital ABC' },
        status: 'open',
        created_at: '2024-01-15T10:00:00Z',
      })
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks,
        },
      })

      expect(wrapper.text()).toContain('Hospital ABC')
      expect(wrapper.text()).toContain('Implement Feature')
      expect(wrapper.text()).toContain('Authentication')
    })

    it('renders "Modul" column for recent variant', () => {
      const tasks = generateTasks(1, { modul: 'Billing' })
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks,
        },
      })

      // Check that Modul header is present
      expect(wrapper.text()).toContain('Modul')
      // Check that modul value is rendered
      expect(wrapper.text()).toContain('Billing')
    })

    it('renders "Tanggal Dibuat" column for recent variant', () => {
      const tasks = generateTasks(1)
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks,
        },
      })

      // Check that Tanggal Dibuat header is present
      expect(wrapper.text()).toContain('Tanggal Dibuat')
    })

    it('displays "-" when modul is missing', () => {
      const tasks = generateTasks(1, { modul: undefined })
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks,
        },
      })

      const cells = wrapper.findAll('td')
      // Modul is the 3rd column (index 2) for recent variant
      const modulCell = cells.find(cell => cell.text() === '-')
      expect(modulCell).toBeDefined()
    })

    it('formats date correctly for Indonesian locale', () => {
      const tasks = generateTasks(1, { created_at: '2024-01-15T00:00:00Z' })
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks,
        },
      })

      // Date is formatted using toLocaleDateString('id-ID')
      // The exact format may vary by environment, but it should contain the date parts
      const text = wrapper.text()
      // Check that date contains month and year
      expect(text).toMatch(/1.*15.*2024|15.*1.*2024/)
    })
  })

  describe("'assigned' variant renders correct columns", () => {
    it('displays "Tugas Anda yang Belum Selesai" title', () => {
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'assigned',
          tasks: [],
        },
      })
      expect(wrapper.text()).toContain('Tugas Anda yang Belum Selesai')
    })

    it('displays description for assigned variant', () => {
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'assigned',
          tasks: [],
        },
      })
      expect(wrapper.text()).toContain('Daftar task yang di-assign ke Anda dan membutuhkan perhatian.')
    })

    it('renders all expected column headers for assigned variant', () => {
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'assigned',
          tasks: [],
        },
      })
      const headers = wrapper.findAll('th')
      const headerTexts = headers.map(h => h.text())

      expect(headerTexts).toContain('Faskes / Client')
      expect(headerTexts).toContain('Judul Task')
      expect(headerTexts).toContain('Prioritas')
      expect(headerTexts).toContain('Status')
    })

    it('renders 4 column headers for assigned variant', () => {
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'assigned',
          tasks: [],
        },
      })
      const headers = wrapper.findAll('th')
      expect(headers.length).toBe(4)
    })

    it('does NOT render "Modul" column for assigned variant', () => {
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'assigned',
          tasks: [],
        },
      })

      const headers = wrapper.findAll('th')
      const headerTexts = headers.map(h => h.text())
      expect(headerTexts).not.toContain('Modul')
    })

    it('does NOT render "Tanggal Dibuat" column for assigned variant', () => {
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'assigned',
          tasks: [],
        },
      })

      const headers = wrapper.findAll('th')
      const headerTexts = headers.map(h => h.text())
      expect(headerTexts).not.toContain('Tanggal Dibuat')
    })

    it('renders "Prioritas" column for assigned variant', () => {
      const tasks = generateTasks(1, { priority: 'urgent' })
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'assigned',
          tasks,
        },
      })

      expect(wrapper.text()).toContain('Prioritas')
      expect(wrapper.text()).toContain('urgent')
    })

    it('displays "-" when priority is missing', () => {
      const tasks = generateTasks(1, { priority: undefined })
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'assigned',
          tasks,
        },
      })

      expect(wrapper.text()).toContain('-')
    })

    it('capitalizes priority text', () => {
      const tasks = generateTasks(1, { priority: 'urgent' })
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'assigned',
          tasks,
        },
      })

      const priorityBadge = wrapper.find('span.capitalize')
      expect(priorityBadge.exists()).toBe(true)
      expect(priorityBadge.text()).toBe('urgent')
    })
  })

  describe('status badge color mapping', () => {
    const statusColors: Array<{ status: TaskStatus; color: string }> = [
      { status: 'open', color: 'amber' },
      { status: 'in_progress', color: 'blue' },
      { status: 'revision', color: 'red' },
      { status: 'completed', color: 'emerald' },
    ]

    statusColors.forEach(({ status, color }) => {
      it(`applies ${color} styling for "${status}" status`, () => {
        const tasks = generateTasks(1, { status: status as TaskStatus })
        const wrapper = mount(TaskListCard, {
          props: {
            variant: 'recent',
            tasks,
          },
        })

        const statusBadge = wrapper.find('span.rounded-full')
        expect(statusBadge.exists()).toBe(true)

        const classes = statusBadge.classes()
        const hasColorClass = classes.some(c => c.includes(`bg-${color}-`) || c.includes(`text-${color}-`))
        expect(hasColorClass).toBe(true)
      })
    })

    it('applies amber styling for "open" status', () => {
      const tasks = generateTasks(1, { status: 'open' })
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks,
        },
      })

      const statusBadge = wrapper.find('span.rounded-full')
      expect(statusBadge.classes()).toContain('bg-amber-100')
      expect(statusBadge.classes()).toContain('text-amber-800')
      expect(statusBadge.classes()).toContain('border-amber-200')
    })

    it('applies blue styling for "in_progress" status', () => {
      const tasks = generateTasks(1, { status: 'in_progress' })
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks,
        },
      })

      const statusBadge = wrapper.find('span.rounded-full')
      expect(statusBadge.classes()).toContain('bg-blue-100')
      expect(statusBadge.classes()).toContain('text-blue-800')
      expect(statusBadge.classes()).toContain('border-blue-200')
    })

    it('applies red styling for "revision" status', () => {
      const tasks = generateTasks(1, { status: 'revision' })
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks,
        },
      })

      const statusBadge = wrapper.find('span.rounded-full')
      expect(statusBadge.classes()).toContain('bg-red-100')
      expect(statusBadge.classes()).toContain('text-red-800')
      expect(statusBadge.classes()).toContain('border-red-200')
    })

    it('applies emerald styling for "completed" status', () => {
      const tasks = generateTasks(1, { status: 'completed' })
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks,
        },
      })

      const statusBadge = wrapper.find('span.rounded-full')
      expect(statusBadge.classes()).toContain('bg-emerald-100')
      expect(statusBadge.classes()).toContain('text-emerald-800')
      expect(statusBadge.classes()).toContain('border-emerald-200')
    })

    it('formats status with spaces instead of underscores', () => {
      const tasks = generateTasks(1, { status: 'in_progress' })
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks,
        },
      })

      const statusBadge = wrapper.find('span.rounded-full')
      expect(statusBadge.text()).toBe('in progress')
    })

    it('applies capitalize class to status badge', () => {
      const tasks = generateTasks(1, { status: 'open' })
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks,
        },
      })

      const statusBadge = wrapper.find('span.rounded-full')
      expect(statusBadge.classes()).toContain('capitalize')
    })
  })

  describe('priority badge color mapping', () => {
    const priorityColors: Array<{ priority: TaskPriority; color: string }> = [
      { priority: 'urgent', color: 'red' },
      { priority: 'high', color: 'amber' },
      { priority: 'medium', color: 'blue' },
      { priority: 'low', color: 'slate' },
    ]

    priorityColors.forEach(({ priority, color }) => {
      it(`applies ${color} styling for "${priority}" priority`, () => {
        const tasks = generateTasks(1, { priority: priority as TaskPriority })
        const wrapper = mount(TaskListCard, {
          props: {
            variant: 'assigned',
            tasks,
          },
        })

        const priorityBadge = wrapper.find('span.rounded-md')
        expect(priorityBadge.exists()).toBe(true)

        const classes = priorityBadge.classes()
        const hasColorClass = classes.some(c => c.includes(`text-${color}-`))
        expect(hasColorClass).toBe(true)
      })
    })

    it('applies red styling for "urgent" priority', () => {
      const tasks = generateTasks(1, { priority: 'urgent' })
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'assigned',
          tasks,
        },
      })

      const priorityBadge = wrapper.find('span.rounded-md')
      expect(priorityBadge.classes()).toContain('border-red-200')
      expect(priorityBadge.classes()).toContain('text-red-600')
    })

    it('applies amber styling for "high" priority', () => {
      const tasks = generateTasks(1, { priority: 'high' })
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'assigned',
          tasks,
        },
      })

      const priorityBadge = wrapper.find('span.rounded-md')
      expect(priorityBadge.classes()).toContain('border-amber-200')
      expect(priorityBadge.classes()).toContain('text-amber-600')
    })

    it('applies blue styling for "medium" priority', () => {
      const tasks = generateTasks(1, { priority: 'medium' })
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'assigned',
          tasks,
        },
      })

      const priorityBadge = wrapper.find('span.rounded-md')
      expect(priorityBadge.classes()).toContain('border-blue-200')
      expect(priorityBadge.classes()).toContain('text-blue-600')
    })

    it('applies slate styling for "low" priority', () => {
      const tasks = generateTasks(1, { priority: 'low' })
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'assigned',
          tasks,
        },
      })

      const priorityBadge = wrapper.find('span.rounded-md')
      expect(priorityBadge.classes()).toContain('border-slate-200')
      expect(priorityBadge.classes()).toContain('text-slate-600')
    })

    it('applies capitalize class to priority badge', () => {
      const tasks = generateTasks(1, { priority: 'urgent' })
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'assigned',
          tasks,
        },
      })

      const priorityBadge = wrapper.find('span.rounded-md')
      expect(priorityBadge.classes()).toContain('capitalize')
    })

    it('priority badge has correct structure', () => {
      const tasks = generateTasks(1, { priority: 'high' })
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'assigned',
          tasks,
        },
      })

      const priorityBadge = wrapper.find('span.rounded-md')
      expect(priorityBadge.classes()).toContain('inline-flex')
      expect(priorityBadge.classes()).toContain('items-center')
      expect(priorityBadge.classes()).toContain('border')
      expect(priorityBadge.classes()).toContain('px-2')
      expect(priorityBadge.classes()).toContain('py-0.5')
      expect(priorityBadge.classes()).toContain('text-xs')
      expect(priorityBadge.classes()).toContain('font-semibold')
    })
  })

  describe('empty state messages', () => {
    it('displays empty message for recent variant when no tasks', () => {
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks: [],
        },
      })

      expect(wrapper.text()).toContain('Belum ada task yang dibuat.')
    })

    it('displays celebratory message for assigned variant when no tasks', () => {
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'assigned',
          tasks: [],
        },
      })

      expect(wrapper.text()).toContain('Hebat! Anda tidak memiliki task yang tertunda. 🎉')
    })

    it('does NOT display empty message when tasks exist for recent variant', () => {
      const tasks = generateTasks(1)
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks,
        },
      })

      expect(wrapper.text()).not.toContain('Belum ada task yang dibuat.')
    })

    it('does NOT display empty message when non-completed tasks exist for assigned variant', () => {
      const tasks = generateTasks(1, { status: 'open' })
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'assigned',
          tasks,
        },
      })

      expect(wrapper.text()).not.toContain('Hebat! Anda tidak memiliki task yang tertunda. 🎉')
    })

    it('displays empty message for assigned variant when all tasks are completed', () => {
      const tasks = generateTasks(3, { status: 'completed' })
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'assigned',
          tasks,
        },
      })

      // Assigned variant filters out completed tasks
      expect(wrapper.text()).toContain('Hebat! Anda tidak memiliki task yang tertunda. 🎉')
    })

    it('uses correct colspan for empty state row (recent)', () => {
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks: [],
        },
      })

      const emptyRow = wrapper.find('tbody tr td[colspan]')
      expect(emptyRow.attributes('colspan')).toBe('5')
    })

    it('uses correct colspan for empty state row (assigned)', () => {
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'assigned',
          tasks: [],
        },
      })

      const emptyRow = wrapper.find('tbody tr td[colspan]')
      expect(emptyRow.attributes('colspan')).toBe('4')
    })

    it('empty state has proper styling', () => {
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks: [],
        },
      })

      const emptyCell = wrapper.find('tbody tr td')
      expect(emptyCell.classes()).toContain('py-8')
      expect(emptyCell.classes()).toContain('text-center')
      expect(emptyCell.classes()).toContain('text-muted-foreground')
    })
  })

  describe('loading state', () => {
    it('displays loading skeleton when loading is true', () => {
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks: [],
          loading: true,
        },
      })

      expect(wrapper.find('.animate-pulse').exists()).toBe(true)
    })

    it('hides table content when loading is true', () => {
      const tasks = generateTasks(3)
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks,
          loading: true,
        },
      })

      // Should not display table with data
      expect(wrapper.find('table').exists()).toBe(false)
    })

    it('shows table content when loading is false (default)', () => {
      const tasks = generateTasks(1)
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks,
          loading: false,
        },
      })

      expect(wrapper.find('table').exists()).toBe(true)
      expect(wrapper.text()).toContain('Task 1')
    })

    it('has skeleton placeholder elements', () => {
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks: [],
          loading: true,
        },
      })

      const skeleton = wrapper.find('.animate-pulse')
      const skeletonBars = skeleton.findAll('div[class*="bg-muted"]')
      expect(skeletonBars.length).toBeGreaterThanOrEqual(2)
    })

    it('applies pulse animation to skeleton', () => {
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks: [],
          loading: true,
        },
      })

      expect(wrapper.find('.animate-pulse').exists()).toBe(true)
    })

    it('maintains header during loading state', () => {
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks: [],
          loading: true,
        },
      })

      // Header should still be visible
      expect(wrapper.text()).toContain('5 Task Terbaru')
    })
  })

  describe('task filtering for assigned variant', () => {
    it('filters out completed tasks for assigned variant', () => {
      const tasks = [
        generateTask(1, { status: 'open', title: 'Open Task' }),
        generateTask(2, { status: 'in_progress', title: 'In Progress Task' }),
        generateTask(3, { status: 'completed', title: 'Completed Task' }),
        generateTask(4, { status: 'revision', title: 'Revision Task' }),
      ]
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'assigned',
          tasks,
        },
      })

      expect(wrapper.text()).toContain('Open Task')
      expect(wrapper.text()).toContain('In Progress Task')
      expect(wrapper.text()).toContain('Revision Task')
      expect(wrapper.text()).not.toContain('Completed Task')
    })

    it('does NOT filter tasks for recent variant', () => {
      const tasks = [
        generateTask(1, { status: 'open', title: 'Open Task' }),
        generateTask(2, { status: 'completed', title: 'Completed Task' }),
      ]
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks,
        },
      })

      expect(wrapper.text()).toContain('Open Task')
      expect(wrapper.text()).toContain('Completed Task')
    })

    it('shows empty state when all tasks are completed for assigned variant', () => {
      const tasks = [
        generateTask(1, { status: 'completed' }),
        generateTask(2, { status: 'completed' }),
      ]
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'assigned',
          tasks,
        },
      })

      expect(wrapper.text()).toContain('Hebat! Anda tidak memiliki task yang tertunda. 🎉')
    })

    it('shows non-completed tasks when some are completed for assigned variant', () => {
      const tasks = [
        generateTask(1, { status: 'open', title: 'Active Task' }),
        generateTask(2, { status: 'completed', title: 'Done Task' }),
      ]
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'assigned',
          tasks,
        },
      })

      expect(wrapper.text()).toContain('Active Task')
      expect(wrapper.text()).not.toContain('Done Task')
    })
  })

  describe('task rendering', () => {
    it('renders multiple tasks', () => {
      const tasks = generateTasks(5)
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks,
        },
      })

      const rows = wrapper.findAll('tbody tr')
      // 5 task rows + 0 empty state row
      expect(rows.length).toBe(5)
    })

    it('renders client name correctly', () => {
      const tasks = generateTasks(1, { client: { id: 1, name: 'RS Harapan Sehat' } })
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks,
        },
      })

      expect(wrapper.text()).toContain('RS Harapan Sehat')
    })

    it('displays "-" when client is missing', () => {
      const tasks = generateTasks(1, { client: undefined })
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks,
        },
      })

      expect(wrapper.text()).toContain('-')
    })

    it('renders task title correctly', () => {
      const tasks = generateTasks(1, { title: 'Implement New Feature' })
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks,
        },
      })

      expect(wrapper.text()).toContain('Implement New Feature')
    })

    it('applies hover styling to table rows', () => {
      const tasks = generateTasks(2)
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks,
        },
      })

      const rows = wrapper.findAll('tbody tr')
      rows.forEach(row => {
        expect(row.classes()).toContain('hover:bg-muted/30')
        expect(row.classes()).toContain('transition-colors')
      })
    })
  })

  describe('semantic HTML structure', () => {
    it('uses article element for semantic structure', () => {
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks: [],
        },
      })

      expect(wrapper.find('article').exists()).toBe(true)
    })

    it('uses header element for card header', () => {
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks: [],
        },
      })

      expect(wrapper.find('header').exists()).toBe(true)
    })

    it('uses h2 for title', () => {
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks: [],
        },
      })

      const title = wrapper.find('h2')
      expect(title.exists()).toBe(true)
      expect(title.classes()).toContain('text-lg')
      expect(title.classes()).toContain('font-semibold')
      expect(title.classes()).toContain('text-primary')
    })

    it('has proper table structure with thead and tbody', () => {
      const tasks = generateTasks(2)
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks,
        },
      })

      expect(wrapper.find('table').exists()).toBe(true)
      expect(wrapper.find('thead').exists()).toBe(true)
      expect(wrapper.find('tbody').exists()).toBe(true)
    })

    it('has proper header cell styling', () => {
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks: [],
        },
      })

      const thead = wrapper.find('thead')
      expect(thead.classes()).toContain('bg-muted/50')
      expect(thead.classes()).toContain('text-muted-foreground')
      expect(thead.classes()).toContain('border-b')
      expect(thead.classes()).toContain('border-border')
    })
  })

  describe('dark mode support', () => {
    it('status badges have dark mode classes', () => {
      const tasks = generateTasks(1, { status: 'open' })
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks,
        },
      })

      const statusBadge = wrapper.find('span.rounded-full')
      const classes = statusBadge.classes()
      const hasDarkClasses = classes.some(c => c.startsWith('dark:'))
      expect(hasDarkClasses).toBe(true)
    })

    it('priority badges have dark mode classes', () => {
      const tasks = generateTasks(1, { priority: 'urgent' })
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'assigned',
          tasks,
        },
      })

      const priorityBadge = wrapper.find('span.rounded-md')
      const classes = priorityBadge.classes()
      const hasDarkClasses = classes.some(c => c.startsWith('dark:'))
      expect(hasDarkClasses).toBe(true)
    })

    it('article has dark mode shadow support', () => {
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks: [],
        },
      })

      const article = wrapper.find('article')
      const classes = article.classes()
      const hasDarkShadow = classes.some(c => c.includes('dark:shadow-['))
      expect(hasDarkShadow).toBe(true)
    })
  })

  describe('typical dashboard use cases', () => {
    it('renders recent tasks for admin dashboard', () => {
      const tasks = [
        generateTask(1, { title: 'Setup Database', status: 'completed', modul: 'Backend' }),
        generateTask(2, { title: 'Create UI Components', status: 'in_progress', modul: 'Frontend' }),
        generateTask(3, { title: 'Write Tests', status: 'open', modul: 'QA' }),
      ]
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'recent',
          tasks,
        },
      })

      expect(wrapper.text()).toContain('5 Task Terbaru')
      expect(wrapper.text()).toContain('Setup Database')
      expect(wrapper.text()).toContain('Create UI Components')
      expect(wrapper.text()).toContain('Write Tests')
    })

    it('renders assigned tasks for member dashboard', () => {
      const tasks = [
        generateTask(1, { title: 'Fix Bug #123', status: 'open', priority: 'urgent' }),
        generateTask(2, { title: 'Review PR #456', status: 'in_progress', priority: 'high' }),
        generateTask(3, { title: 'Update Docs', status: 'revision', priority: 'medium' }),
      ]
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'assigned',
          tasks,
        },
      })

      expect(wrapper.text()).toContain('Tugas Anda yang Belum Selesai')
      expect(wrapper.text()).toContain('Fix Bug #123')
      expect(wrapper.text()).toContain('Review PR #456')
      expect(wrapper.text()).toContain('Update Docs')
      // Should show priority badges
      expect(wrapper.text()).toContain('urgent')
      expect(wrapper.text()).toContain('high')
      expect(wrapper.text()).toContain('medium')
    })

    it('member dashboard shows no completed tasks', () => {
      const tasks = [
        generateTask(1, { title: 'Active Task', status: 'open' }),
        generateTask(2, { title: 'Completed Task', status: 'completed' }),
      ]
      const wrapper = mount(TaskListCard, {
        props: {
          variant: 'assigned',
          tasks,
        },
      })

      expect(wrapper.text()).toContain('Active Task')
      expect(wrapper.text()).not.toContain('Completed Task')
    })
  })
})
