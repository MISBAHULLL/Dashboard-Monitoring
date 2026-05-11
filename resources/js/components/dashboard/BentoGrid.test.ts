/**
 * BentoGrid Component Unit Tests
 *
 * Tests the BentoGrid container component for proper CSS Grid layout,
 * responsive column classes, and gap configuration.
 *
 */

import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import BentoGrid from './BentoGrid.vue'
import type { GridColumns } from '@/types/dashboard'

describe('BentoGrid', () => {
  describe('renders with default props', () => {
    it('renders a div element', () => {
      const wrapper = mount(BentoGrid)
      expect(wrapper.find('div').exists()).toBe(true)
    })

    it('has the grid class by default', () => {
      const wrapper = mount(BentoGrid)
      expect(wrapper.find('div').classes()).toContain('grid')
    })

    it('has auto-rows-min class by default', () => {
      const wrapper = mount(BentoGrid)
      expect(wrapper.find('div').classes()).toContain('auto-rows-min')
    })

    it('applies default column class (grid-cols-1)', () => {
      const wrapper = mount(BentoGrid)
      expect(wrapper.find('div').classes()).toContain('grid-cols-1')
    })

    it('applies default responsive column classes', () => {
      const wrapper = mount(BentoGrid)
      const classes = wrapper.find('div').classes()
      expect(classes).toContain('md:grid-cols-2')
      expect(classes).toContain('lg:grid-cols-4')
    })

    it('applies default gap classes', () => {
      const wrapper = mount(BentoGrid)
      const classes = wrapper.find('div').classes()
      expect(classes).toContain('gap-4')
    })

    it('renders slot content', () => {
      const wrapper = mount(BentoGrid, {
        slots: {
          default: '<div class="test-content">Test Content</div>',
        },
      })
      expect(wrapper.find('.test-content').exists()).toBe(true)
      expect(wrapper.text()).toContain('Test Content')
    })
  })

  describe('responsive column classes at different breakpoints', () => {
    it('applies single column configuration', () => {
      const columns: GridColumns = { default: 1 }
      const wrapper = mount(BentoGrid, {
        props: { columns },
      })
      const classes = wrapper.find('div').classes()
      expect(classes).toContain('grid-cols-1')
      expect(classes).not.toContain('md:grid-cols-2')
      expect(classes).not.toContain('lg:grid-cols-4')
    })

    it('applies 2-column configuration at default breakpoint', () => {
      const columns: GridColumns = { default: 2 }
      const wrapper = mount(BentoGrid, {
        props: { columns },
      })
      expect(wrapper.find('div').classes()).toContain('grid-cols-2')
    })

    it('applies 3-column configuration at default breakpoint', () => {
      const columns: GridColumns = { default: 3 }
      const wrapper = mount(BentoGrid, {
        props: { columns },
      })
      expect(wrapper.find('div').classes()).toContain('grid-cols-3')
    })

    it('applies 4-column configuration at default breakpoint', () => {
      const columns: GridColumns = { default: 4 }
      const wrapper = mount(BentoGrid, {
        props: { columns },
      })
      expect(wrapper.find('div').classes()).toContain('grid-cols-4')
    })

    it('applies responsive md breakpoint column class', () => {
      const columns: GridColumns = { default: 1, md: 2 }
      const wrapper = mount(BentoGrid, {
        props: { columns },
      })
      expect(wrapper.find('div').classes()).toContain('md:grid-cols-2')
    })

    it('applies responsive lg breakpoint column class', () => {
      const columns: GridColumns = { default: 1, lg: 3 }
      const wrapper = mount(BentoGrid, {
        props: { columns },
      })
      expect(wrapper.find('div').classes()).toContain('lg:grid-cols-3')
    })

    it('applies responsive xl breakpoint column class', () => {
      const columns: GridColumns = { default: 1, xl: 4 }
      const wrapper = mount(BentoGrid, {
        props: { columns },
      })
      expect(wrapper.find('div').classes()).toContain('xl:grid-cols-4')
    })

    it('applies all responsive breakpoint column classes together', () => {
      const columns: GridColumns = { default: 1, md: 2, lg: 3, xl: 4 }
      const wrapper = mount(BentoGrid, {
        props: { columns },
      })
      const classes = wrapper.find('div').classes()
      expect(classes).toContain('grid-cols-1')
      expect(classes).toContain('md:grid-cols-2')
      expect(classes).toContain('lg:grid-cols-3')
      expect(classes).toContain('xl:grid-cols-4')
    })

    it('does not apply md breakpoint class when md is undefined', () => {
      const columns: GridColumns = { default: 1, lg: 3 }
      const wrapper = mount(BentoGrid, {
        props: { columns },
      })
      const classes = wrapper.find('div').classes()
      expect(classes).not.toContain('md:grid-cols-undefined')
      expect(classes).not.toContain('md:grid-cols-2')
    })

    it('does not apply lg breakpoint class when lg is undefined', () => {
      const columns: GridColumns = { default: 1, md: 2 }
      const wrapper = mount(BentoGrid, {
        props: { columns },
      })
      const classes = wrapper.find('div').classes()
      expect(classes).not.toContain('lg:grid-cols-undefined')
    })

    it('does not apply xl breakpoint class when xl is undefined', () => {
      const columns: GridColumns = { default: 1, md: 2, lg: 3 }
      const wrapper = mount(BentoGrid, {
        props: { columns },
      })
      const classes = wrapper.find('div').classes()
      expect(classes).not.toContain('xl:grid-cols-undefined')
    })
  })

  describe('custom gap classes', () => {
    it('applies custom gap class', () => {
      const wrapper = mount(BentoGrid, {
        props: { gap: 'gap-2' },
      })
      expect(wrapper.find('div').classes()).toContain('gap-2')
    })

    it('applies custom responsive gap classes', () => {
      const wrapper = mount(BentoGrid, {
        props: { gap: 'gap-8 md:gap-10 lg:gap-12' },
      })
      const classes = wrapper.find('div').classes()
      expect(classes).toContain('gap-8')
      expect(classes).toContain('md:gap-10')
      expect(classes).toContain('lg:gap-12')
    })

    it('applies gap-0 class when specified', () => {
      const wrapper = mount(BentoGrid, {
        props: { gap: 'gap-0' },
      })
      expect(wrapper.find('div').classes()).toContain('gap-0')
    })

    it('overrides default gap with custom gap', () => {
      const wrapper = mount(BentoGrid, {
        props: { gap: 'gap-8' },
      })
      const classes = wrapper.find('div').classes()
      // Custom gap is applied
      expect(classes).toContain('gap-8')
      // Default gap-4 is not applied (string replacement)
      expect(classes).not.toContain('gap-4')
    })

    it('maintains grid and auto-rows-min classes with custom gap', () => {
      const wrapper = mount(BentoGrid, {
        props: { gap: 'gap-2' },
      })
      const classes = wrapper.find('div').classes()
      expect(classes).toContain('grid')
      expect(classes).toContain('auto-rows-min')
    })
  })

  describe('combined props', () => {
    it('applies both custom columns and gap correctly', () => {
      const columns: GridColumns = { default: 2, md: 3, lg: 4 }
      const wrapper = mount(BentoGrid, {
        props: {
          columns,
          gap: 'gap-2 md:gap-4',
        },
      })
      const classes = wrapper.find('div').classes()
      // Column classes
      expect(classes).toContain('grid-cols-2')
      expect(classes).toContain('md:grid-cols-3')
      expect(classes).toContain('lg:grid-cols-4')
      // Gap classes
      expect(classes).toContain('gap-2')
      expect(classes).toContain('md:gap-4')
      // Base classes
      expect(classes).toContain('grid')
      expect(classes).toContain('auto-rows-min')
    })
  })
})
