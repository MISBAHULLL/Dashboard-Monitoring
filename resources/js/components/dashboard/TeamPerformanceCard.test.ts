/**
 * TeamPerformanceCard Component Unit Tests
 *
 * Tests the TeamPerformanceCard component for proper rendering with props,
 * team data display, completion rate badges, empty states, loading states,
 * and semantic HTML structure.
 *
 * @see
 */

import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import TeamPerformanceCard from './TeamPerformanceCard.vue'
import type { TeamPerformance } from '@/types/dashboard'

/**
 * Helper to generate a team performance object for testing
 */
const generateTeam = (id: number, overrides: Partial<TeamPerformance> = {}): TeamPerformance => ({
  id,
  name: `Team ${id}`,
  type: 'product',
  total_tasks: 10 + id,
  completed_tasks: 5 + id,
  open_tasks: 2,
  in_progress_tasks: 2,
  revision_tasks: 1,
  overdue_tasks: id,
  completion_rate: 50 + id,
  ...overrides,
})

/**
 * Helper to generate an array of teams
 */
const generateTeams = (count: number): TeamPerformance[] =>
  Array.from({ length: count }, (_, i) => generateTeam(i + 1))

describe('TeamPerformanceCard', () => {
  describe('renders team data correctly', () => {
    it('renders an article element', () => {
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams: [],
        },
      })
      expect(wrapper.find('article').exists()).toBe(true)
    })

    it('renders the header title', () => {
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams: [],
        },
      })
      expect(wrapper.text()).toContain('Ringkasan Performa Tim Product')
    })

    it('renders the header subtitle', () => {
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams: [],
        },
      })
      expect(wrapper.text()).toContain('Progress task per tim berdasarkan total, selesai, dan overdue.')
    })

    it('renders team name in table row', () => {
      const teams = generateTeams(1)
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      expect(wrapper.text()).toContain('Team 1')
    })

    it('renders total tasks for each team', () => {
      const teams = generateTeams(1)
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      expect(wrapper.text()).toContain('11') // 10 + 1 from generateTeam
    })

    it('renders completed tasks count for each team', () => {
      const teams = generateTeams(1)
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      expect(wrapper.text()).toContain('6') // 5 + 1 from generateTeam
    })

    it('renders overdue tasks count for each team', () => {
      const teams = generateTeams(1)
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      expect(wrapper.text()).toContain('1') // id from generateTeam
    })

    it('renders multiple teams correctly', () => {
      const teams = generateTeams(3)
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      expect(wrapper.text()).toContain('Team 1')
      expect(wrapper.text()).toContain('Team 2')
      expect(wrapper.text()).toContain('Team 3')
    })

    it('applies neo-brutalism base styles', () => {
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams: [],
        },
      })
      const article = wrapper.find('article')
      expect(article.classes()).toContain('rounded-xl')
      expect(article.classes()).toContain('border-[1.5px]')
      expect(article.classes()).toContain('border-border')
      expect(article.classes()).toContain('bg-card')
    })

    it('applies shadow styles', () => {
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams: [],
        },
      })
      const article = wrapper.find('article')
      // Shadow class should be present
      const hasShadow = article.classes().some(c => c.includes('shadow-['))
      expect(hasShadow).toBe(true)
    })

    it('renders table with correct column headers', () => {
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams: [],
        },
      })
      const headerCells = wrapper.findAll('th')
      expect(headerCells.length).toBe(5)
      expect(headerCells[0].text()).toBe('Tim')
      expect(headerCells[1].text()).toBe('Total')
      expect(headerCells[2].text()).toBe('Selesai')
      expect(headerCells[3].text()).toBe('Overdue')
      expect(headerCells[4].text()).toBe('Completion Rate')
    })
  })

  describe('completion rate badge displays', () => {
    it('renders completion rate as badge', () => {
      const teams = generateTeams(1)
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      const badge = wrapper.find('span.inline-flex.items-center.rounded-full')
      expect(badge.exists()).toBe(true)
    })

    it('displays completion rate percentage', () => {
      const teams = generateTeams(1)
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      const badge = wrapper.find('span.inline-flex.items-center.rounded-full')
      expect(badge.text()).toContain('51%') // 50 + 1 from generateTeam
    })

    it('applies navy pale background to badge', () => {
      const teams = generateTeams(1)
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      const badge = wrapper.find('span.inline-flex.items-center.rounded-full')
      expect(badge.classes()).toContain('bg-tm-navy-pale')
    })

    it('applies navy text color to badge', () => {
      const teams = generateTeams(1)
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      const badge = wrapper.find('span.inline-flex.items-center.rounded-full')
      expect(badge.classes()).toContain('text-tm-navy')
    })

    it('displays 0% completion rate correctly', () => {
      const teams = [generateTeam(1, { completion_rate: 0 })]
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      const badge = wrapper.find('span.inline-flex.items-center.rounded-full')
      expect(badge.text()).toBe('0%')
    })

    it('displays 100% completion rate correctly', () => {
      const teams = [generateTeam(1, { completion_rate: 100 })]
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      const badge = wrapper.find('span.inline-flex.items-center.rounded-full')
      expect(badge.text()).toBe('100%')
    })

    it('displays decimal completion rate correctly', () => {
      const teams = [generateTeam(1, { completion_rate: 75.5 })]
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      const badge = wrapper.find('span.inline-flex.items-center.rounded-full')
      expect(badge.text()).toBe('75.5%')
    })

    it('applies correct font styling to badge', () => {
      const teams = generateTeams(1)
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      const badge = wrapper.find('span.inline-flex.items-center.rounded-full')
      expect(badge.classes()).toContain('text-xs')
      expect(badge.classes()).toContain('font-semibold')
    })
  })

  describe('empty state message', () => {
    it('displays empty state message when no teams', () => {
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams: [],
        },
      })
      expect(wrapper.text()).toContain('Belum ada data performa tim.')
    })

    it('does not display empty state when teams exist', () => {
      const teams = generateTeams(1)
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      expect(wrapper.text()).not.toContain('Belum ada data performa tim.')
    })

    it('empty state message spans all 5 columns', () => {
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams: [],
        },
      })
      const emptyRow = wrapper.find('tr:last-child td')
      expect(emptyRow.attributes('colspan')).toBe('5')
    })

    it('empty state has correct text styling', () => {
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams: [],
        },
      })
      const emptyMessage = wrapper.find('td.text-center.text-muted-foreground')
      expect(emptyMessage.exists()).toBe(true)
    })

    it('empty state has appropriate padding', () => {
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams: [],
        },
      })
      const emptyMessage = wrapper.find('td.py-8')
      expect(emptyMessage.exists()).toBe(true)
    })
  })

  describe('loading skeleton', () => {
    it('displays loading skeleton when loading is true', () => {
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams: [],
          loading: true,
        },
      })
      expect(wrapper.find('.animate-pulse').exists()).toBe(true)
    })

    it('hides content when loading is true', () => {
      const teams = generateTeams(3)
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
          loading: true,
        },
      })
      // Should not display team names in loading state
      expect(wrapper.text()).not.toContain('Team 1')
      expect(wrapper.text()).not.toContain('Team 2')
      expect(wrapper.text()).not.toContain('Team 3')
    })

    it('shows content when loading is false (default)', () => {
      const teams = generateTeams(1)
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
          loading: false,
        },
      })
      expect(wrapper.text()).toContain('Team 1')
    })

    it('has skeleton placeholder elements for table structure', () => {
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams: [],
          loading: true,
        },
      })
      const skeleton = wrapper.find('.animate-pulse')
      // Should have skeleton bars for header and rows
      const skeletonBars = skeleton.findAll('div[class*="bg-muted"]')
      expect(skeletonBars.length).toBeGreaterThanOrEqual(4)
    })

    it('applies pulse animation to skeleton', () => {
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams: [],
          loading: true,
        },
      })
      expect(wrapper.find('.animate-pulse').exists()).toBe(true)
    })

    it('skeleton maintains component dimensions', () => {
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams: [],
          loading: true,
        },
      })
      const skeleton = wrapper.find('.animate-pulse')
      // Should have height classes
      const skeletonBars = skeleton.findAll('div.h-10')
      expect(skeletonBars.length).toBeGreaterThan(0)
    })

    it('shows header even when loading', () => {
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams: [],
          loading: true,
        },
      })
      // Header should still be visible in loading state
      expect(wrapper.text()).toContain('Ringkasan Performa Tim Product')
    })
  })

  describe('semantic HTML structure', () => {
    it('uses article element for semantic structure', () => {
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams: [],
        },
      })
      expect(wrapper.find('article').exists()).toBe(true)
    })

    it('uses header element for card header', () => {
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams: [],
        },
      })
      expect(wrapper.find('header').exists()).toBe(true)
    })

    it('uses h2 for card title', () => {
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams: [],
        },
      })
      expect(wrapper.find('h2').exists()).toBe(true)
    })

    it('uses semantic table elements', () => {
      const teams = generateTeams(1)
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      expect(wrapper.find('table').exists()).toBe(true)
      expect(wrapper.find('thead').exists()).toBe(true)
      expect(wrapper.find('tbody').exists()).toBe(true)
    })

    it('has proper table header structure', () => {
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams: [],
        },
      })
      const thead = wrapper.find('thead')
      expect(thead.find('tr').exists()).toBe(true)
      expect(thead.findAll('th').length).toBe(5)
    })

    it('has proper table body structure', () => {
      const teams = generateTeams(2)
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      const tbody = wrapper.find('tbody')
      const rows = tbody.findAll('tr')
      // Should have rows for each team
      expect(rows.length).toBeGreaterThanOrEqual(2)
    })

    it('uses th elements with font-medium class', () => {
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams: [],
        },
      })
      const headers = wrapper.findAll('th')
      headers.forEach(header => {
        expect(header.classes()).toContain('font-medium')
      })
    })
  })

  describe('hover and transition classes', () => {
    it('has transition classes on article', () => {
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams: [],
        },
      })
      const article = wrapper.find('article')
      expect(article.classes()).toContain('transition-all')
      expect(article.classes()).toContain('duration-200')
      expect(article.classes()).toContain('ease-out')
    })

    it('has hover shadow class on article', () => {
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams: [],
        },
      })
      const article = wrapper.find('article')
      const hasHoverShadow = article.classes().some(c => c.includes('hover:shadow-['))
      expect(hasHoverShadow).toBe(true)
    })

    it('has hover transition on table rows', () => {
      const teams = generateTeams(1)
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      const row = wrapper.find('tbody tr')
      expect(row.classes()).toContain('hover:bg-muted/30')
      expect(row.classes()).toContain('transition-colors')
    })
  })

  describe('completed tasks styling', () => {
    it('applies green color to completed tasks count', () => {
      const teams = generateTeams(1)
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      const completedCell = wrapper.find('td.text-tm-green')
      expect(completedCell.exists()).toBe(true)
    })

    it('applies font-semibold to completed tasks count', () => {
      const teams = generateTeams(1)
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      const completedCell = wrapper.find('td.text-tm-green.font-semibold')
      expect(completedCell.exists()).toBe(true)
    })
  })

  describe('overdue tasks styling', () => {
    it('applies danger color to overdue tasks count', () => {
      const teams = generateTeams(1)
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      const overdueCell = wrapper.find('td.text-tm-danger')
      expect(overdueCell.exists()).toBe(true)
    })

    it('applies font-semibold to overdue tasks count', () => {
      const teams = generateTeams(1)
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      const overdueCell = wrapper.find('td.text-tm-danger.font-semibold')
      expect(overdueCell.exists()).toBe(true)
    })
  })

  describe('responsive design', () => {
    it('has overflow-x-auto for horizontal scroll on mobile', () => {
      const teams = generateTeams(1)
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      const scrollContainer = wrapper.find('.overflow-x-auto')
      expect(scrollContainer.exists()).toBe(true)
    })

    it('table takes full width', () => {
      const teams = generateTeams(1)
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      const table = wrapper.find('table')
      expect(table.classes()).toContain('w-full')
    })
  })

  describe('typical dashboard use cases', () => {
    it('renders single team correctly', () => {
      const teams = [generateTeam(1, {
        name: 'Development Team',
        total_tasks: 50,
        completed_tasks: 35,
        overdue_tasks: 5,
        completion_rate: 70,
      })]
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      expect(wrapper.text()).toContain('Development Team')
      expect(wrapper.text()).toContain('50')
      expect(wrapper.text()).toContain('35')
      expect(wrapper.text()).toContain('5')
      expect(wrapper.text()).toContain('70%')
    })

    it('renders multiple teams with different metrics', () => {
      const teams = [
        generateTeam(1, { name: 'Team Alpha', total_tasks: 100, completion_rate: 80 }),
        generateTeam(2, { name: 'Team Beta', total_tasks: 75, completion_rate: 60 }),
        generateTeam(3, { name: 'Team Gamma', total_tasks: 50, completion_rate: 90 }),
      ]
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      expect(wrapper.text()).toContain('Team Alpha')
      expect(wrapper.text()).toContain('Team Beta')
      expect(wrapper.text()).toContain('Team Gamma')
      expect(wrapper.text()).toContain('80%')
      expect(wrapper.text()).toContain('60%')
      expect(wrapper.text()).toContain('90%')
    })

    it('renders team with zero overdue correctly', () => {
      const teams = [generateTeam(1, { overdue_tasks: 0 })]
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      expect(wrapper.text()).toContain('0')
    })

    it('renders team with zero completed correctly', () => {
      const teams = [generateTeam(1, { completed_tasks: 0, completion_rate: 0 })]
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      expect(wrapper.text()).toContain('0')
    })

    it('renders team with all completed tasks', () => {
      const teams = [generateTeam(1, {
        total_tasks: 20,
        completed_tasks: 20,
        open_tasks: 0,
        in_progress_tasks: 0,
        revision_tasks: 0,
        overdue_tasks: 0,
        completion_rate: 100,
      })]
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      expect(wrapper.text()).toContain('100%')
    })
  })

  describe('table cell alignment', () => {
    it('center-aligns numeric columns', () => {
      const teams = generateTeams(1)
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })

      // Check header alignment
      const headers = wrapper.findAll('th')
      expect(headers[1].classes()).toContain('text-center') // Total
      expect(headers[2].classes()).toContain('text-center') // Selesai
      expect(headers[3].classes()).toContain('text-center') // Overdue
      expect(headers[4].classes()).toContain('text-center') // Completion Rate
    })

    it('left-aligns team name column', () => {
      const teams = generateTeams(1)
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })

      const firstHeader = wrapper.find('th')
      // The table has text-left class by default on the element
      expect(firstHeader.classes()).not.toContain('text-center')

      const teamCell = wrapper.find('tbody td:first-child')
      expect(teamCell.classes()).not.toContain('text-center')
    })
  })

  describe('dark mode classes', () => {
    it('has dark mode shadow class', () => {
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams: [],
        },
      })
      const article = wrapper.find('article')
      const hasDarkShadow = article.classes().some(c =>
        c.includes('dark:shadow-[')
      )
      expect(hasDarkShadow).toBe(true)
    })

    it('completion rate badge has dark mode background', () => {
      const teams = generateTeams(1)
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      const badge = wrapper.find('span.inline-flex.items-center.rounded-full')
      expect(badge.classes()).toContain('dark:bg-tm-navy/20')
    })

    it('completion rate badge has dark mode text color', () => {
      const teams = generateTeams(1)
      const wrapper = mount(TeamPerformanceCard, {
        props: {
          teams,
        },
      })
      const badge = wrapper.find('span.inline-flex.items-center.rounded-full')
      expect(badge.classes()).toContain('dark:text-tm-navy-pale')
    })
  })
})
