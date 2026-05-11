/**
 * TeamPerformanceCard Component Property-Based Tests
 *
 * Tests universal correctness properties for team performance ordering.
 * Uses property-based testing with fast-check to verify that for all possible
 * team data arrays, the component correctly sorts and limits the display.
 *
 * Property 9: Team Performance Ordering
 * - Generate random arrays of team data
 * - Verify rendered teams are sorted by total_tasks descending
 * - Verify maximum 10 teams displayed
 *
 * @see
 */

import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import * as fc from 'fast-check'
import TeamPerformanceCard from './TeamPerformanceCard.vue'
import type { TeamPerformance } from '@/types/dashboard'

/**
 * Arbitrary for generating valid TeamPerformance objects
 * Generates realistic team data with proper constraints
 */
const teamPerformanceArbitrary = fc.record({
  id: fc.integer({ min: 1, max: 10000 }),
  name: fc.string({ minLength: 1, maxLength: 50 }),
  type: fc.constantFrom('product', 'implementation', 'support', 'qa'),
  total_tasks: fc.integer({ min: 0, max: 1000 }),
  completed_tasks: fc.integer({ min: 0, max: 1000 }),
  open_tasks: fc.integer({ min: 0, max: 1000 }),
  in_progress_tasks: fc.integer({ min: 0, max: 1000 }),
  revision_tasks: fc.integer({ min: 0, max: 1000 }),
  overdue_tasks: fc.integer({ min: 0, max: 1000 }),
  completion_rate: fc.integer({ min: 0, max: 100 }),
}) as fc.Arbitrary<TeamPerformance>

/**
 * Arbitrary for generating arrays of TeamPerformance with varying lengths
 * Including lengths > 10 to test the "maximum 10 teams" property
 */
const teamPerformanceArrayArbitrary = fc.array(teamPerformanceArbitrary, {
  minLength: 0,
  maxLength: 50, // Test arrays larger than 10 to verify truncation
})

describe('TeamPerformanceCard Property-Based Tests', () => {
  describe('Property 9: Team Performance Ordering', () => {
    /**
     * Property: For any array of team data, the rendered teams should be
     * sorted by total_tasks in descending order.
     *
     * This verifies that regardless of the input order, the component
     * displays teams with highest total_tasks first.
     *
     * @validates Requirements 6.5
     */
    it('renders teams sorted by total_tasks descending', () => {
      fc.assert(
        fc.property(
          teamPerformanceArrayArbitrary,
          (teams) => {
            const wrapper = mount(TeamPerformanceCard, {
              props: { teams },
            })

            // Find all team rows (exclude empty state row)
            const teamRows = wrapper.findAll('tbody tr').filter((row) => {
              // Empty state row has colspan attribute
              const hasColspan = row.find('td[colspan]').exists()
              return !hasColspan
            })

            // Skip if no teams or only empty state
            if (teamRows.length === 0) {
              return true
            }

            // Extract total_tasks values from rendered rows
            const renderedTotalTasks: number[] = []
            teamRows.forEach((row) => {
              const cells = row.findAll('td')
              if (cells.length >= 2) {
                // Second cell (index 1) contains total_tasks
                const totalText = cells[1].text().trim()
                const total = parseInt(totalText, 10)
                if (!isNaN(total)) {
                  renderedTotalTasks.push(total)
                }
              }
            })

            // Verify descending order
            for (let i = 0; i < renderedTotalTasks.length - 1; i++) {
              expect(renderedTotalTasks[i]).toBeGreaterThanOrEqual(
                renderedTotalTasks[i + 1]
              )
            }

            return true
          }
        )
      )
    })

    /**
     * Property: For any array of team data with more than 10 teams,
     * the component should render at most 10 teams.
     *
     * @validates Requirements 6.5
     */
    it('renders at most 10 teams regardless of input array length', () => {
      fc.assert(
        fc.property(
          teamPerformanceArrayArbitrary,
          (teams) => {
            const wrapper = mount(TeamPerformanceCard, {
              props: { teams },
            })

            // Find all team rows (exclude empty state row)
            const teamRows = wrapper.findAll('tbody tr').filter((row) => {
              // Empty state row has colspan attribute
              const hasColspan = row.find('td[colspan]').exists()
              return !hasColspan
            })

            // Should never exceed 10 team rows
            expect(teamRows.length).toBeLessThanOrEqual(10)

            return true
          }
        )
      )
    })

    /**
     * Property: For any array of team data, the component should render
     * all teams if there are 10 or fewer, or exactly 10 teams if there are more.
     *
     * @validates Requirements 6.5
     */
    it('renders exactly min(teams.length, 10) teams', () => {
      fc.assert(
        fc.property(
          teamPerformanceArrayArbitrary,
          (teams) => {
            const wrapper = mount(TeamPerformanceCard, {
              props: { teams },
            })

            // Find all team rows (exclude empty state row)
            const teamRows = wrapper.findAll('tbody tr').filter((row) => {
              const hasColspan = row.find('td[colspan]').exists()
              return !hasColspan
            })

            const expectedCount = Math.min(teams.length, 10)
            expect(teamRows.length).toBe(expectedCount)

            return true
          }
        )
      )
    })

    /**
     * Property: For any array of team data, the top 10 teams by total_tasks
     * should be the ones displayed.
     *
     * @validates Requirements 6.5
     */
    it('displays the top 10 teams by total_tasks', () => {
      fc.assert(
        fc.property(
          teamPerformanceArrayArbitrary,
          (teams) => {
            const wrapper = mount(TeamPerformanceCard, {
              props: { teams },
            })

            // Find all team rows
            const teamRows = wrapper.findAll('tbody tr').filter((row) => {
              const hasColspan = row.find('td[colspan]').exists()
              return !hasColspan
            })

            if (teams.length === 0) {
              expect(teamRows.length).toBe(0)
              return true
            }

            // Get the expected top 10 teams
            const expectedTopTeams = [...teams]
              .sort((a, b) => b.total_tasks - a.total_tasks)
              .slice(0, 10)

            // Extract team names from rendered rows
            const renderedTeamNames: string[] = []
            teamRows.forEach((row) => {
              const cells = row.findAll('td')
              if (cells.length > 0) {
                const name = cells[0].text().trim()
                renderedTeamNames.push(name)
              }
            })

            // Get expected team names (trimmed to match Vue's whitespace handling)
            const expectedTeamNames = expectedTopTeams.map((t) => t.name.trim())

            // Verify the team names match (in the same order)
            expect(renderedTeamNames).toEqual(expectedTeamNames)

            return true
          }
        )
      )
    })

    /**
     * Property: For any array of team data, each rendered row should display
     * the correct team name, total_tasks, completed_tasks, overdue_tasks, and
     * completion_rate.
     *
     * @validates Requirements 6.1, 6.5
     */
    it('displays correct data for each rendered team', () => {
      fc.assert(
        fc.property(
          teamPerformanceArrayArbitrary,
          (teams) => {
            const wrapper = mount(TeamPerformanceCard, {
              props: { teams },
            })

            // Get the top 10 teams by total_tasks (what should be rendered)
            const expectedTopTeams = [...teams]
              .sort((a, b) => b.total_tasks - a.total_tasks)
              .slice(0, 10)

            // Find all team rows
            const teamRows = wrapper.findAll('tbody tr').filter((row) => {
              const hasColspan = row.find('td[colspan]').exists()
              return !hasColspan
            })

            // Verify each row contains correct data
            teamRows.forEach((row, index) => {
              const expectedTeam = expectedTopTeams[index]
              const cells = row.findAll('td')

              if (cells.length >= 5) {
                // Verify team name (trimmed to match Vue's whitespace handling)
                expect(cells[0].text().trim()).toBe(expectedTeam.name.trim())

                // Verify total_tasks
                expect(cells[1].text().trim()).toBe(
                  expectedTeam.total_tasks.toString()
                )

                // Verify completed_tasks
                expect(cells[2].text().trim()).toBe(
                  expectedTeam.completed_tasks.toString()
                )

                // Verify overdue_tasks
                expect(cells[3].text().trim()).toBe(
                  expectedTeam.overdue_tasks.toString()
                )

                // Verify completion_rate (includes %)
                expect(cells[4].text().trim()).toBe(
                  `${expectedTeam.completion_rate}%`
                )
              }
            })

            return true
          }
        )
      )
    })

    /**
     * Property: For any array of team data, teams with equal total_tasks
     * should be rendered (order among equal teams may vary).
     *
     * @validates Requirements 6.5
     */
    it('handles teams with equal total_tasks values correctly', () => {
      // Generate teams with specific total_tasks values to test ties
      const teamsWithTiesArbitrary = fc.array(
        fc.record({
          id: fc.integer({ min: 1, max: 10000 }),
          name: fc.string({ minLength: 1, maxLength: 50 }),
          type: fc.constantFrom('product', 'implementation', 'support', 'qa'),
          // Use a constrained set of total_tasks values to create ties
          total_tasks: fc.constantFrom(10, 20, 30, 40, 50),
          completed_tasks: fc.integer({ min: 0, max: 50 }),
          open_tasks: fc.integer({ min: 0, max: 50 }),
          in_progress_tasks: fc.integer({ min: 0, max: 50 }),
          revision_tasks: fc.integer({ min: 0, max: 50 }),
          overdue_tasks: fc.integer({ min: 0, max: 50 }),
          completion_rate: fc.integer({ min: 0, max: 100 }),
        }) as fc.Arbitrary<TeamPerformance>,
        { minLength: 0, maxLength: 20 }
      )

      fc.assert(
        fc.property(
          teamsWithTiesArbitrary,
          (teams) => {
            const wrapper = mount(TeamPerformanceCard, {
              props: { teams },
            })

            // Find all team rows
            const teamRows = wrapper.findAll('tbody tr').filter((row) => {
              const hasColspan = row.find('td[colspan]').exists()
              return !hasColspan
            })

            if (teamRows.length === 0) {
              return true
            }

            // Extract total_tasks values
            const renderedTotalTasks: number[] = []
            teamRows.forEach((row) => {
              const cells = row.findAll('td')
              if (cells.length >= 2) {
                const total = parseInt(cells[1].text().trim(), 10)
                if (!isNaN(total)) {
                  renderedTotalTasks.push(total)
                }
              }
            })

            // Verify descending order (even with ties)
            for (let i = 0; i < renderedTotalTasks.length - 1; i++) {
              expect(renderedTotalTasks[i]).toBeGreaterThanOrEqual(
                renderedTotalTasks[i + 1]
              )
            }

            // Verify at most 10 teams
            expect(teamRows.length).toBeLessThanOrEqual(10)

            return true
          }
        )
      )
    })

    /**
     * Property: For any array of team data with 0 teams, the component
     * should display the empty state message.
     *
     * @validates Requirements 6.4, 6.5
     */
    it('displays empty state when no teams provided', () => {
      fc.assert(
        fc.property(
          fc.constant([] as TeamPerformance[]),
          (teams) => {
            const wrapper = mount(TeamPerformanceCard, {
              props: { teams },
            })

            // Should find the empty state row
            const emptyRow = wrapper.find('tbody tr td[colspan="5"]')
            expect(emptyRow.exists()).toBe(true)

            // Should contain empty state message
            expect(emptyRow.text()).toContain('Belum ada data performa tim.')

            return true
          }
        )
      )
    })

    /**
     * Property: For any array of team data, the component should render
     * the completion rate badge with correct styling classes.
     *
     * @validates Requirements 6.3, 6.5
     */
    it('renders completion rate badge with correct styling for any team', () => {
      fc.assert(
        fc.property(
          teamPerformanceArrayArbitrary.filter((teams) => teams.length > 0),
          (teams) => {
            const wrapper = mount(TeamPerformanceCard, {
              props: { teams },
            })

            // Find all completion rate badges
            const badges = wrapper.findAll('span.rounded-full')

            // Each badge should have navy-related classes
            badges.forEach((badge) => {
              const classes = badge.classes()

              // Should have background color class
              const hasNavyBg =
                classes.includes('bg-tm-navy-pale') ||
                classes.includes('dark:bg-tm-navy/20')
              expect(hasNavyBg).toBe(true)

              // Should have text color class
              const hasNavyText =
                classes.includes('text-tm-navy') ||
                classes.includes('dark:text-tm-navy-pale')
              expect(hasNavyText).toBe(true)
            })

            return true
          }
        )
      )
    })

    /**
     * Property: For any array of team data, the rendered table should have
     * exactly 5 columns (Team, Total, Selesai, Overdue, Completion Rate).
     *
     * @validates Requirements 6.1, 6.5
     */
    it('renders table with exactly 5 columns for any team data', () => {
      fc.assert(
        fc.property(
          teamPerformanceArrayArbitrary,
          (teams) => {
            const wrapper = mount(TeamPerformanceCard, {
              props: { teams },
            })

            // Find header row
            const headerRow = wrapper.find('thead tr')
            const headerCells = headerRow.findAll('th')

            // Should have exactly 5 header cells
            expect(headerCells.length).toBe(5)

            // Verify header labels
            expect(headerCells[0].text()).toBe('Tim')
            expect(headerCells[1].text()).toBe('Total')
            expect(headerCells[2].text()).toBe('Selesai')
            expect(headerCells[3].text()).toBe('Overdue')
            expect(headerCells[4].text()).toBe('Completion Rate')

            return true
          }
        )
      )
    })
  })

  describe('Property: Team Data Integrity', () => {
    /**
     * Property: For any valid team data, the component should render
     * without errors and produce consistent output.
     *
     * @validates Requirements 6.1-6.5
     */
    it('renders successfully for any valid team data', () => {
      fc.assert(
        fc.property(
          teamPerformanceArrayArbitrary,
          (teams) => {
            // Should not throw
            const wrapper = mount(TeamPerformanceCard, {
              props: { teams },
            })

            // Should render an article element
            expect(wrapper.find('article').exists()).toBe(true)

            // Should render a table element
            expect(wrapper.find('table').exists()).toBe(true)

            return true
          }
        )
      )
    })

    /**
     * Property: For any team data, the loading state should hide the table
     * and show the skeleton instead.
     *
     * @validates Requirements 16.1-16.5
     */
    it('shows loading skeleton when loading prop is true', () => {
      fc.assert(
        fc.property(
          teamPerformanceArrayArbitrary,
          (teams) => {
            const wrapper = mount(TeamPerformanceCard, {
              props: { teams, loading: true },
            })

            // Should show loading skeleton
            const skeleton = wrapper.find('.animate-pulse')
            expect(skeleton.exists()).toBe(true)

            // Should not show table (it's in v-else)
            const table = wrapper.find('table')
            expect(table.exists()).toBe(false)

            return true
          }
        )
      )
    })

    /**
     * Property: For any team data, the component should display correct
     * semantic HTML structure.
     *
     * @validates Requirements 12.1, 16.1
     */
    it('uses semantic HTML elements for any team data', () => {
      fc.assert(
        fc.property(
          teamPerformanceArrayArbitrary,
          (teams) => {
            const wrapper = mount(TeamPerformanceCard, {
              props: { teams },
            })

            // Should use article as root
            expect(wrapper.find('article').exists()).toBe(true)

            // Should use header for title
            expect(wrapper.find('article > div > header').exists()).toBe(true)

            // Should use table with proper semantic elements
            expect(wrapper.find('thead').exists()).toBe(true)
            expect(wrapper.find('tbody').exists()).toBe(true)

            return true
          }
        )
      )
    })
  })
})
