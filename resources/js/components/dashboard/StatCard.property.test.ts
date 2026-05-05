/**
 * StatCard Component Property-Based Tests
 *
 * Tests universal correctness properties for StatCard color theme mapping.
 * Uses property-based testing with fast-check to verify that for all possible
 * colorTheme values, the component applies correct background, border, and text color classes.
 *
 * Property 1: Color Theme Mapping
 * - Generate random colorTheme values
 * - Verify component has correct background, border, and text color classes
 *
 * @see Requirements: 2.3, 3.3, 3.4, 3.5, 8.2, 8.3, 8.4
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

/**
 * Color theme to TailwindCSS class mappings
 * These are the expected classes for each color theme based on the design spec
 */
const COLOR_THEME_CLASSES: Record<ColorTheme, {
  background: string[]
  border: string[]
  iconBackground: string[]
  iconColor: string[]
  valueColor: string[]
}> = {
  navy: {
    background: ['bg-tm-navy-pale', 'dark:bg-tm-navy/20'],
    border: ['border-tm-navy'],
    iconBackground: ['bg-tm-navy/10'],
    iconColor: ['text-tm-navy'],
    valueColor: ['text-tm-navy'],
  },
  green: {
    background: ['bg-tm-green-pale', 'dark:bg-tm-green/20'],
    border: ['border-tm-green'],
    iconBackground: ['bg-tm-green/10'],
    iconColor: ['text-tm-green'],
    valueColor: ['text-tm-green-dark'],
  },
  red: {
    background: ['bg-tm-danger-pale', 'dark:bg-tm-danger/20'],
    border: ['border-tm-danger'],
    iconBackground: ['bg-tm-danger/10'],
    iconColor: ['text-tm-danger'],
    valueColor: ['text-tm-danger'],
  },
  amber: {
    background: ['bg-tm-warning-pale', 'dark:bg-tm-warning/20'],
    border: ['border-tm-warning'],
    iconBackground: ['bg-tm-warning/10'],
    iconColor: ['text-tm-warning'],
    valueColor: ['text-amber-700', 'dark:text-amber-400'],
  },
  neutral: {
    background: ['bg-card'],
    border: ['border-border'],
    iconBackground: ['bg-muted'],
    iconColor: ['text-muted-foreground'],
    valueColor: ['text-foreground'],
  },
}

/**
 * Valid color theme values
 */
const VALID_COLOR_THEMES: ColorTheme[] = ['navy', 'green', 'red', 'amber', 'neutral']

describe('StatCard Property-Based Tests', () => {
  describe('Property 1: Color Theme Mapping', () => {
    /**
     * Property: For any valid colorTheme, the article element must have the correct
     * background class(es) from the COLOR_THEME_CLASSES mapping.
     *
     * @validates Requirements 2.3, 3.3, 3.4, 3.5, 8.2, 8.3, 8.4
     */
    it('applies correct background class for any colorTheme', () => {
      fc.assert(
        fc.property(
          fc.constantFrom(...VALID_COLOR_THEMES),
          fc.string({ minLength: 1 }),
          fc.oneof(fc.integer(), fc.string()),
          (colorTheme, label, value) => {
            const wrapper = mount(StatCard, {
              props: {
                label,
                value,
                icon: MockIcon,
                colorTheme,
              },
            })

            const article = wrapper.find('article')
            const classes = article.classes()
            const expectedBgClasses = COLOR_THEME_CLASSES[colorTheme].background

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
     * Property: For any valid colorTheme, the article element must have the correct
     * border class from the COLOR_THEME_CLASSES mapping.
     *
     * @validates Requirements 2.3, 3.3, 3.4, 3.5, 8.2, 8.3, 8.4
     */
    it('applies correct border class for any colorTheme', () => {
      fc.assert(
        fc.property(
          fc.constantFrom(...VALID_COLOR_THEMES),
          fc.string({ minLength: 1 }),
          fc.oneof(fc.integer(), fc.string()),
          (colorTheme, label, value) => {
            const wrapper = mount(StatCard, {
              props: {
                label,
                value,
                icon: MockIcon,
                colorTheme,
              },
            })

            const article = wrapper.find('article')
            const classes = article.classes()
            const expectedBorderClasses = COLOR_THEME_CLASSES[colorTheme].border

            // Border class should be present
            const hasExpectedBorder = expectedBorderClasses.some(borderClass =>
              classes.includes(borderClass)
            )
            expect(hasExpectedBorder).toBe(true)
          }
        )
      )
    })

    /**
     * Property: For any valid colorTheme, the icon container must have the correct
     * background class from the COLOR_THEME_CLASSES mapping.
     *
     * @validates Requirements 2.3, 3.3, 3.4, 3.5, 8.2, 8.3, 8.4
     */
    it('applies correct icon background class for any colorTheme', () => {
      fc.assert(
        fc.property(
          fc.constantFrom(...VALID_COLOR_THEMES),
          fc.string({ minLength: 1 }),
          fc.oneof(fc.integer(), fc.string()),
          (colorTheme, label, value) => {
            const wrapper = mount(StatCard, {
              props: {
                label,
                value,
                icon: MockIcon,
                colorTheme,
              },
            })

            // Find icon container (div with rounded-xl, p-3, border-[1.5px])
            const iconContainer = wrapper.find('div.rounded-xl.p-3.border-\\[1\\.5px\\]')
            expect(iconContainer.exists()).toBe(true)

            const classes = iconContainer.classes()
            const expectedIconBgClasses = COLOR_THEME_CLASSES[colorTheme].iconBackground

            // Icon background class should be present
            const hasExpectedIconBg = expectedIconBgClasses.some(iconBgClass =>
              classes.includes(iconBgClass)
            )
            expect(hasExpectedIconBg).toBe(true)
          }
        )
      )
    })

    /**
     * Property: For any valid colorTheme, the icon must have the correct
     * color class from the COLOR_THEME_CLASSES mapping.
     *
     * @validates Requirements 2.3, 3.3, 3.4, 3.5, 8.2, 8.3, 8.4
     */
    it('applies correct icon color class for any colorTheme', () => {
      fc.assert(
        fc.property(
          fc.constantFrom(...VALID_COLOR_THEMES),
          fc.string({ minLength: 1 }),
          fc.oneof(fc.integer(), fc.string()),
          (colorTheme, label, value) => {
            const wrapper = mount(StatCard, {
              props: {
                label,
                value,
                icon: MockIcon,
                colorTheme,
              },
            })

            const icon = wrapper.find('svg.mock-icon')
            expect(icon.exists()).toBe(true)

            const classes = icon.classes()
            const expectedIconColorClasses = COLOR_THEME_CLASSES[colorTheme].iconColor

            // Icon color class should be present
            const hasExpectedIconColor = expectedIconColorClasses.some(iconColorClass =>
              classes.includes(iconColorClass)
            )
            expect(hasExpectedIconColor).toBe(true)
          }
        )
      )
    })

    /**
     * Property: For any valid colorTheme, the value text must have the correct
     * color class from the COLOR_THEME_CLASSES mapping.
     *
     * @validates Requirements 2.3, 3.3, 3.4, 3.5, 8.2, 8.3, 8.4
     */
    it('applies correct value color class for any colorTheme', () => {
      fc.assert(
        fc.property(
          fc.constantFrom(...VALID_COLOR_THEMES),
          fc.string({ minLength: 1 }),
          fc.oneof(fc.integer(), fc.string()),
          (colorTheme, label, value) => {
            const wrapper = mount(StatCard, {
              props: {
                label,
                value,
                icon: MockIcon,
                colorTheme,
              },
            })

            const valueElement = wrapper.find('p.text-3xl.font-bold')
            expect(valueElement.exists()).toBe(true)

            const classes = valueElement.classes()
            const expectedValueColorClasses = COLOR_THEME_CLASSES[colorTheme].valueColor

            // Value color class should be present
            const hasExpectedValueColor = expectedValueColorClasses.some(valueColorClass =>
              classes.includes(valueColorClass)
            )
            expect(hasExpectedValueColor).toBe(true)
          }
        )
      )
    })

    /**
     * Property: For any valid colorTheme, all color-related classes must be consistent
     * and match the expected theme mapping.
     *
     * This is a comprehensive property that verifies the entire theme mapping at once.
     *
     * @validates Requirements 2.3, 3.3, 3.4, 3.5, 8.2, 8.3, 8.4
     */
    it('applies all correct theme classes consistently for any colorTheme', () => {
      fc.assert(
        fc.property(
          fc.constantFrom(...VALID_COLOR_THEMES),
          fc.string({ minLength: 1 }),
          fc.oneof(fc.integer(), fc.string()),
          (colorTheme, label, value) => {
            const wrapper = mount(StatCard, {
              props: {
                label,
                value,
                icon: MockIcon,
                colorTheme,
              },
            })

            const expectedClasses = COLOR_THEME_CLASSES[colorTheme]

            // Verify article has correct background
            const article = wrapper.find('article')
            const articleClasses = article.classes()
            expect(
              expectedClasses.background.some(bg => articleClasses.includes(bg))
            ).toBe(true)
            expect(
              expectedClasses.border.some(b => articleClasses.includes(b))
            ).toBe(true)

            // Verify icon container has correct background
            const iconContainer = wrapper.find('div.rounded-xl.p-3.border-\\[1\\.5px\\]')
            const iconContainerClasses = iconContainer.classes()
            expect(
              expectedClasses.iconBackground.some(ibg => iconContainerClasses.includes(ibg))
            ).toBe(true)

            // Verify icon has correct color
            const icon = wrapper.find('svg.mock-icon')
            const iconClasses = icon.classes()
            expect(
              expectedClasses.iconColor.some(ic => iconClasses.includes(ic))
            ).toBe(true)

            // Verify value has correct color
            const valueElement = wrapper.find('p.text-3xl.font-bold')
            const valueClasses = valueElement.classes()
            expect(
              expectedClasses.valueColor.some(vc => valueClasses.includes(vc))
            ).toBe(true)
          }
        )
      )
    })

    /**
     * Property: For any colorTheme value passed, the component must render
     * without errors and produce consistent output.
     *
     * This tests that the component handles all valid colorTheme values gracefully.
     *
     * @validates Requirements 2.3, 3.3, 3.4, 3.5, 8.2, 8.3, 8.4
     */
    it('renders successfully for any valid colorTheme', () => {
      fc.assert(
        fc.property(
          fc.constantFrom(...VALID_COLOR_THEMES),
          fc.string({ minLength: 1, maxLength: 100 }),
          fc.oneof(fc.integer({ min: 0, max: 1000000 }), fc.string({ maxLength: 50 })),
          (colorTheme, label, value) => {
            // Should not throw
            const wrapper = mount(StatCard, {
              props: {
                label,
                value,
                icon: MockIcon,
                colorTheme,
              },
            })

            // Should render an article element
            expect(wrapper.find('article').exists()).toBe(true)

            // Should have some classes (not empty)
            const articleClasses = wrapper.find('article').classes()
            expect(articleClasses.length).toBeGreaterThan(0)
          }
        )
      )
    })
  })

  describe('Property: Neo-Brutalism Base Styles', () => {
    /**
     * Property: For any colorTheme, the neo-brutalism base styles must be applied
     * (rounded-xl, border-[1.5px], p-6, shadow).
     *
     * @validates Requirements 2.1, 2.2
     */
    it('applies neo-brutalism base styles for any colorTheme', () => {
      fc.assert(
        fc.property(
          fc.constantFrom(...VALID_COLOR_THEMES),
          (colorTheme) => {
            const wrapper = mount(StatCard, {
              props: {
                label: 'Test',
                value: 100,
                icon: MockIcon,
                colorTheme,
              },
            })

            const article = wrapper.find('article')
            const classes = article.classes()

            // Base neo-brutalism styles
            expect(classes).toContain('rounded-xl')
            expect(classes).toContain('border-[1.5px]')
            expect(classes).toContain('p-6')

            // Shadow class
            const hasShadow = classes.some(c => c.includes('shadow-['))
            expect(hasShadow).toBe(true)
          }
        )
      )
    })
  })

  describe('Property: Consistent Theme Application', () => {
    /**
     * Property: For any sequence of colorTheme changes, the component must update
     * its classes to match the new theme.
     *
     * @validates Requirements 2.3, 3.3, 3.4, 3.5
     */
    it('updates theme classes when colorTheme prop changes', async () => {
      fc.assert(
        fc.asyncProperty(
          fc.constantFrom(...VALID_COLOR_THEMES),
          fc.constantFrom(...VALID_COLOR_THEMES),
          async (initialTheme, newTheme) => {
            const wrapper = mount(StatCard, {
              props: {
                label: 'Test',
                value: 100,
                icon: MockIcon,
                colorTheme: initialTheme,
              },
            })

            // Check initial theme
            const initialBorderClass = COLOR_THEME_CLASSES[initialTheme].border[0]
            const article = wrapper.find('article')
            expect(article.classes()).toContain(initialBorderClass)

            // Change theme
            await wrapper.setProps({ colorTheme: newTheme })

            // Check new theme
            const newBorderClass = COLOR_THEME_CLASSES[newTheme].border[0]
            expect(article.classes()).toContain(newBorderClass)

            // Old theme border should be removed (unless same theme)
            if (initialTheme !== newTheme) {
              expect(article.classes()).not.toContain(initialBorderClass)
            }
          }
        )
      )
    })

    /**
     * Property: Each colorTheme should produce unique border class
     * (except for cases where themes share the same border color).
     *
     * @validates Requirements 2.3, 3.3, 3.4, 3.5, 8.2, 8.3, 8.4
     */
    it('produces distinct border classes for different colorThemes', () => {
      const borderClasses = new Set<string>()

      VALID_COLOR_THEMES.forEach((colorTheme) => {
        const wrapper = mount(StatCard, {
          props: {
            label: 'Test',
            value: 100,
            icon: MockIcon,
            colorTheme,
          },
        })

        const article = wrapper.find('article')
        const expectedBorder = COLOR_THEME_CLASSES[colorTheme].border[0]
        borderClasses.add(expectedBorder)

        // Verify the border class is actually applied
        expect(article.classes()).toContain(expectedBorder)
      })

      // All border classes should be unique (5 themes = 5 unique border classes)
      expect(borderClasses.size).toBe(VALID_COLOR_THEMES.length)
    })
  })
})
