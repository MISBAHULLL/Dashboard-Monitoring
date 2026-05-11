/**
 * StatCard Component Unit Tests
 *
 * Tests the StatCard component for proper rendering with props,
 * color theme variants, loading states, hover animations, and accessibility.
 *
 */

import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import { h, defineComponent } from 'vue'
import * as fc from 'fast-check'
import StatCard from './StatCard.vue'
import type { ColorTheme } from '@/types/dashboard'

/**
 * Mock icon component for testing
 * Simulates a Lucide icon with consistent structure
 */
const MockIcon = defineComponent({
  name: 'MockIcon',
  setup(_, { slots }) {
    return () => h('svg', { class: 'mock-icon' }, slots.default?.())
  },
})

describe('StatCard', () => {
  describe('renders with all required props', () => {
    it('renders an article element', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 100,
          icon: MockIcon,
        },
      })
      expect(wrapper.find('article').exists()).toBe(true)
    })

    it('renders the label text', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 100,
          icon: MockIcon,
        },
      })
      expect(wrapper.text()).toContain('Total Tasks')
    })

    it('renders the numeric value', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 100,
          icon: MockIcon,
        },
      })
      expect(wrapper.text()).toContain('100')
    })

    it('renders the string value', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Status',
          value: 'Active',
          icon: MockIcon,
        },
      })
      expect(wrapper.text()).toContain('Active')
    })

    it('renders the icon component', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 100,
          icon: MockIcon,
        },
      })
      expect(wrapper.find('svg.mock-icon').exists()).toBe(true)
    })

    it('applies default color theme (neutral)', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 100,
          icon: MockIcon,
        },
      })
      const article = wrapper.find('article')
      expect(article.classes()).toContain('bg-card')
      expect(article.classes()).toContain('border-border')
    })

    it('applies neo-brutalism base styles', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 100,
          icon: MockIcon,
        },
      })
      const article = wrapper.find('article')
      const classString = article.classes().join(' ')
      // Check for base styling classes
      expect(classString).toContain('relative')
      expect(classString).toContain('overflow-hidden')
      expect(classString).toContain('border-2')
      // Normal mode should have p-6
      expect(classString).toMatch(/\bp-6\b/)
    })

    it('applies shadow styles', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 100,
          icon: MockIcon,
        },
      })
      const article = wrapper.find('article')
      // Shadow class should be present (format: shadow-[2px_2px_0_0_rgba(0,0,0,0.08)])
      const hasShadow = article.classes().some(c => c.includes('shadow-['))
      expect(hasShadow).toBe(true)
    })
  })

  describe('all 5 color theme variants', () => {
    it('applies navy theme classes', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Teams',
          value: 50,
          icon: MockIcon,
          colorTheme: 'navy',
        },
      })
      const article = wrapper.find('article')
      expect(article.classes()).toContain('bg-tm-navy-pale')
      expect(article.classes()).toContain('border-tm-navy')
    })

    it('applies green theme classes', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Completed Tasks',
          value: 75,
          icon: MockIcon,
          colorTheme: 'green',
        },
      })
      const article = wrapper.find('article')
      expect(article.classes()).toContain('bg-tm-green-pale')
      expect(article.classes()).toContain('border-tm-green')
    })

    it('applies red theme classes', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Overdue Tasks',
          value: 5,
          icon: MockIcon,
          colorTheme: 'red',
        },
      })
      const article = wrapper.find('article')
      expect(article.classes()).toContain('bg-tm-danger-pale')
      expect(article.classes()).toContain('border-tm-danger')
    })

    it('applies amber theme classes', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Open Tasks',
          value: 25,
          icon: MockIcon,
          colorTheme: 'amber',
        },
      })
      const article = wrapper.find('article')
      expect(article.classes()).toContain('bg-tm-warning-pale')
      expect(article.classes()).toContain('border-tm-warning')
    })

    it('applies neutral theme classes (default)', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 100,
          icon: MockIcon,
          colorTheme: 'neutral',
        },
      })
      const article = wrapper.find('article')
      expect(article.classes()).toContain('bg-card')
      expect(article.classes()).toContain('border-border')
    })

    it('applies correct value color class for each theme', () => {
      const themes: ColorTheme[] = ['navy', 'green', 'red', 'amber', 'neutral']

      themes.forEach((theme) => {
        const wrapper = mount(StatCard, {
          props: {
            label: 'Test',
            value: 1,
            icon: MockIcon,
            colorTheme: theme,
          },
        })

        // Find all p elements and check for the value element
        const pElements = wrapper.findAll('p')
        // Value element should have font-bold and either text-3xl (normal) or text-xl (compact)
        const valueElement = pElements.find(p => {
          const text = p.text()
          return text === '1' // The value we're looking for
        })
        expect(valueElement).toBeDefined()
        // Value element should have a text color class
        const classString = valueElement!.classes().join(' ')
        const hasTextColor = classString.split(' ').some(c => c.startsWith('text-'))
        expect(hasTextColor).toBe(true)
      })
    })

    it('applies correct icon background and color for each theme', () => {
      const themes: ColorTheme[] = ['navy', 'green', 'red', 'amber', 'neutral']

      themes.forEach((theme) => {
        const wrapper = mount(StatCard, {
          props: {
            label: 'Test',
            value: 1,
            icon: MockIcon,
            colorTheme: theme,
          },
        })

        // Icon container should have theme classes
        const iconContainer = wrapper.find('div.rounded-xl.border-2')
        expect(iconContainer.exists()).toBe(true)
        const hasIconBg = iconContainer.classes().some(c => c.includes('bg-tm-') || c.includes('bg-muted'))
        expect(hasIconBg).toBe(true)
      })
    })
  })

  describe('loading skeleton displays when loading=true', () => {
    it('displays loading skeleton when loading is true', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 100,
          icon: MockIcon,
          loading: true,
        },
      })
      expect(wrapper.find('.animate-pulse').exists()).toBe(true)
    })

    it('hides content when loading is true', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 100,
          icon: MockIcon,
          loading: true,
        },
      })
      // Should not display label or value in loading state
      expect(wrapper.text()).not.toContain('Total Tasks')
      expect(wrapper.text()).not.toContain('100')
    })

    it('shows content when loading is false (default)', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 100,
          icon: MockIcon,
          loading: false,
        },
      })
      expect(wrapper.text()).toContain('Total Tasks')
      expect(wrapper.text()).toContain('100')
    })

    it('has skeleton placeholder elements', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 100,
          icon: MockIcon,
          loading: true,
        },
      })
      const skeleton = wrapper.find('.animate-pulse')
      // Should have skeleton bars for label and value
      const skeletonBars = skeleton.findAll('div[class*="bg-muted"]')
      expect(skeletonBars.length).toBeGreaterThanOrEqual(2)
    })

    it('applies pulse animation to skeleton', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 100,
          icon: MockIcon,
          loading: true,
        },
      })
      expect(wrapper.find('.animate-pulse').exists()).toBe(true)
    })
  })

  describe('hover classes are applied', () => {
    it('has transition classes by default (animate=true)', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 100,
          icon: MockIcon,
        },
      })
      const article = wrapper.find('article')
      expect(article.classes()).toContain('transition-all')
      expect(article.classes()).toContain('duration-200')
      expect(article.classes()).toContain('ease-out')
    })

    it('has hover shadow class when animate=true', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 100,
          icon: MockIcon,
          animate: true,
        },
      })
      const article = wrapper.find('article')
      const hasHoverShadow = article.classes().some(c => c.includes('hover:shadow-['))
      expect(hasHoverShadow).toBe(true)
    })

    it('has hover translate class when animate=true', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 100,
          icon: MockIcon,
          animate: true,
        },
      })
      const article = wrapper.find('article')
      expect(article.classes()).toContain('hover:-translate-y-0.5')
    })

    it('does not have hover classes when animate=false', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 100,
          icon: MockIcon,
          animate: false,
        },
      })
      const article = wrapper.find('article')
      expect(article.classes()).not.toContain('transition-all')
      expect(article.classes()).not.toContain('hover:-translate-y-0.5')
    })

    it('applies all hover animation classes together when animate=true', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 100,
          icon: MockIcon,
          animate: true,
        },
      })
      const article = wrapper.find('article')
      const classes = article.classes()

      // Should have all animation-related classes
      expect(classes).toContain('transition-all')
      expect(classes).toContain('duration-200')
      expect(classes).toContain('ease-out')
      expect(classes).toContain('hover:-translate-y-0.5')

      // Should have hover shadow
      const hasHoverShadow = classes.some(c => c.includes('hover:shadow-['))
      expect(hasHoverShadow).toBe(true)
    })
  })

  describe('ARIA label format', () => {
    it('has aria-label attribute on article element', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 100,
          icon: MockIcon,
        },
      })
      expect(wrapper.find('article').attributes('aria-label')).toBeDefined()
    })

    it('formats aria-label as "{label}: {value}" for numeric value', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 100,
          icon: MockIcon,
        },
      })
      expect(wrapper.find('article').attributes('aria-label')).toBe('Total Tasks: 100')
    })

    it('formats aria-label as "{label}: {value}" for string value', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Status',
          value: 'Active',
          icon: MockIcon,
        },
      })
      expect(wrapper.find('article').attributes('aria-label')).toBe('Status: Active')
    })

    it('updates aria-label when props change', async () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 100,
          icon: MockIcon,
        },
      })

      expect(wrapper.find('article').attributes('aria-label')).toBe('Total Tasks: 100')

      await wrapper.setProps({ label: 'Open Tasks', value: 25 })

      expect(wrapper.find('article').attributes('aria-label')).toBe('Open Tasks: 25')
    })

    it('handles special characters in label and value', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Tasks (Overdue)',
          value: '10%',
          icon: MockIcon,
        },
      })
      expect(wrapper.find('article').attributes('aria-label')).toBe('Tasks (Overdue): 10%')
    })

    it('handles zero value correctly', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Overdue Tasks',
          value: 0,
          icon: MockIcon,
        },
      })
      expect(wrapper.find('article').attributes('aria-label')).toBe('Overdue Tasks: 0')
    })

    it('handles large numbers correctly', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Records',
          value: 1000000,
          icon: MockIcon,
        },
      })
      expect(wrapper.find('article').attributes('aria-label')).toBe('Total Records: 1000000')
    })
  })

  /**
   * Property-based tests for ARIA label format
   *
   * **Validates: Requirements 12.2**
   * Property 6: ARIA Label Format - verifies aria-label matches expected format "{label}: {value}"
   */
  describe('Property: ARIA label format', () => {
    it('always formats aria-label as "{label}: {value}" for any string label and string value', () => {
      fc.assert(
        fc.property(
          fc.string({ minLength: 1, maxLength: 100 }),
          fc.string({ minLength: 1, maxLength: 50 }),
          (label, value) => {
            const wrapper = mount(StatCard, {
              props: {
                label,
                value,
                icon: MockIcon,
              },
            })
            const ariaLabel = wrapper.find('article').attributes('aria-label')
            return ariaLabel === `${label}: ${value}`
          }
        )
      )
    })

    it('always formats aria-label as "{label}: {value}" for any string label and numeric value', () => {
      fc.assert(
        fc.property(
          fc.string({ minLength: 1, maxLength: 100 }),
          fc.integer({ min: 0, max: 1000000 }),
          (label, value) => {
            const wrapper = mount(StatCard, {
              props: {
                label,
                value,
                icon: MockIcon,
              },
            })
            const ariaLabel = wrapper.find('article').attributes('aria-label')
            return ariaLabel === `${label}: ${value}`
          }
        )
      )
    })

    it('always formats aria-label correctly with realistic dashboard labels and numeric values', () => {
      const dashboardLabels = fc.constantFrom(
        'Total Tasks',
        'Open Tasks',
        'Completed Tasks',
        'Overdue Tasks',
        'Total Clients',
        'Total Teams',
        'Active Users',
        'Pending Items',
        'In Progress',
        'Completed'
      )

      fc.assert(
        fc.property(
          dashboardLabels,
          fc.integer({ min: 0, max: 100000 }),
          (label, value) => {
            const wrapper = mount(StatCard, {
              props: {
                label,
                value,
                icon: MockIcon,
              },
            })
            const ariaLabel = wrapper.find('article').attributes('aria-label')
            return ariaLabel === `${label}: ${value}`
          }
        )
      )
    })

    it('always formats aria-label correctly with realistic dashboard labels and string values', () => {
      const dashboardLabels = fc.constantFrom(
        'Status',
        'Priority',
        'Progress',
        'Health',
        'Rating'
      )

      const dashboardValues = fc.constantFrom(
        'Active',
        'Inactive',
        'High',
        'Medium',
        'Low',
        'Excellent',
        'Good',
        'Fair',
        'Poor'
      )

      fc.assert(
        fc.property(
          dashboardLabels,
          dashboardValues,
          (label, value) => {
            const wrapper = mount(StatCard, {
              props: {
                label,
                value,
                icon: MockIcon,
              },
            })
            const ariaLabel = wrapper.find('article').attributes('aria-label')
            return ariaLabel === `${label}: ${value}`
          }
        )
      )
    })
  })

  describe('semantic HTML structure', () => {
    it('uses article element for semantic structure', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 100,
          icon: MockIcon,
        },
      })
      expect(wrapper.find('article').exists()).toBe(true)
    })

    it('has proper text hierarchy with paragraph elements', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 100,
          icon: MockIcon,
        },
      })
      const paragraphs = wrapper.findAll('p')
      expect(paragraphs.length).toBeGreaterThanOrEqual(2) // Label and value
    })

    it('label has text-muted-foreground class', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 100,
          icon: MockIcon,
        },
      })
      const labelElement = wrapper.find('p.text-sm.font-medium')
      expect(labelElement.exists()).toBe(true)
      expect(labelElement.classes()).toContain('text-muted-foreground')
    })

    it('value has text-3xl font-bold classes', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 100,
          icon: MockIcon,
        },
      })
      // Find all p elements and check for the value element
      const pElements = wrapper.findAll('p')
      const valueElement = pElements.find(p => p.text() === '100')
      expect(valueElement).toBeDefined()
      const classString = valueElement!.classes().join(' ')
      expect(classString).toContain('font-bold')
      // Should have text-3xl for normal mode (not compact)
      expect(classString).toContain('text-3xl')
    })
  })

  describe('icon container styling', () => {
    it('has rounded corners on icon container', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 100,
          icon: MockIcon,
        },
      })
      const iconContainer = wrapper.find('div.rounded-xl')
      expect(iconContainer.exists()).toBe(true)
    })

    it('has padding on icon container', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 100,
          icon: MockIcon,
        },
      })
      // Find all divs and check for icon container (the one with the icon)
      const divs = wrapper.findAll('div')
      const iconContainer = divs.find(d => {
        // Icon container should have rounded-xl and border-2
        const classString = d.classes().join(' ')
        return classString.includes('rounded-xl') && classString.includes('border-2') && d.find('svg').exists()
      })
      expect(iconContainer).toBeDefined()
      // Should have p-3 for normal mode (not compact)
      const classString = iconContainer!.classes().join(' ')
      expect(classString).toMatch(/\bp-3\b/)
    })

    it('has border on icon container', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 100,
          icon: MockIcon,
        },
      })
      const iconContainer = wrapper.find('div.rounded-xl')
      expect(iconContainer.classes()).toContain('border-2')
    })

    it('icon has correct size classes', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 100,
          icon: MockIcon,
        },
      })
      const icon = wrapper.find('svg')
      expect(icon.classes()).toContain('h-5')
      expect(icon.classes()).toContain('w-5')
    })
  })

  describe('typical dashboard use cases', () => {
    it('renders Total Tasks stat card (neutral theme)', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tasks',
          value: 150,
          icon: MockIcon,
          colorTheme: 'neutral',
        },
      })
      expect(wrapper.text()).toContain('Total Tasks')
      expect(wrapper.text()).toContain('150')
      expect(wrapper.find('article').classes()).toContain('border-border')
    })

    it('renders Open Tasks stat card (amber theme)', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Open Tasks',
          value: 42,
          icon: MockIcon,
          colorTheme: 'amber',
        },
      })
      expect(wrapper.text()).toContain('Open Tasks')
      expect(wrapper.text()).toContain('42')
      expect(wrapper.find('article').classes()).toContain('border-tm-warning')
    })

    it('renders Total Clients stat card (green theme)', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Faskes',
          value: 25,
          icon: MockIcon,
          colorTheme: 'green',
        },
      })
      expect(wrapper.text()).toContain('Total Faskes')
      expect(wrapper.text()).toContain('25')
      expect(wrapper.find('article').classes()).toContain('border-tm-green')
    })

    it('renders Total Teams stat card (navy theme)', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Total Tim',
          value: 8,
          icon: MockIcon,
          colorTheme: 'navy',
        },
      })
      expect(wrapper.text()).toContain('Total Tim')
      expect(wrapper.text()).toContain('8')
      expect(wrapper.find('article').classes()).toContain('border-tm-navy')
    })

    it('renders Overdue Tasks stat card (red theme)', () => {
      const wrapper = mount(StatCard, {
        props: {
          label: 'Task Overdue',
          value: 5,
          icon: MockIcon,
          colorTheme: 'red',
        },
      })
      expect(wrapper.text()).toContain('Task Overdue')
      expect(wrapper.text()).toContain('5')
      expect(wrapper.find('article').classes()).toContain('border-tm-danger')
    })
  })
})
