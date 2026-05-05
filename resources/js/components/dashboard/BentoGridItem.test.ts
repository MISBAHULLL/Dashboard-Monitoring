/**
 * BentoGridItem Component Unit Tests
 *
 * Tests the BentoGridItem wrapper component for proper CSS Grid column span
 * class application and responsive breakpoint variants.
 *
 * @see Requirements: 1.1
 */

import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import BentoGridItem from './BentoGridItem.vue'
import type { GridSpan } from '@/types/dashboard'

describe('BentoGridItem', () => {
  describe('renders with default props', () => {
    it('renders a div element', () => {
      const wrapper = mount(BentoGridItem)
      expect(wrapper.find('div').exists()).toBe(true)
    })

    it('applies default col-span-1 class', () => {
      const wrapper = mount(BentoGridItem)
      expect(wrapper.find('div').classes()).toContain('col-span-1')
    })

    it('renders slot content', () => {
      const wrapper = mount(BentoGridItem, {
        slots: {
          default: '<div class="test-content">Test Content</div>',
        },
      })
      expect(wrapper.find('.test-content').exists()).toBe(true)
      expect(wrapper.text()).toContain('Test Content')
    })
  })

  describe('span classes are applied correctly', () => {
    it('applies col-span-1 when specified', () => {
      const span: GridSpan = { default: 'col-span-1' }
      const wrapper = mount(BentoGridItem, {
        props: { span },
      })
      expect(wrapper.find('div').classes()).toContain('col-span-1')
    })

    it('applies col-span-2 when specified', () => {
      const span: GridSpan = { default: 'col-span-2' }
      const wrapper = mount(BentoGridItem, {
        props: { span },
      })
      expect(wrapper.find('div').classes()).toContain('col-span-2')
    })

    it('applies col-span-3 when specified', () => {
      const span: GridSpan = { default: 'col-span-3' }
      const wrapper = mount(BentoGridItem, {
        props: { span },
      })
      expect(wrapper.find('div').classes()).toContain('col-span-3')
    })

    it('applies col-span-4 when specified', () => {
      const span: GridSpan = { default: 'col-span-4' }
      const wrapper = mount(BentoGridItem, {
        props: { span },
      })
      expect(wrapper.find('div').classes()).toContain('col-span-4')
    })

    it('applies col-span-full for full-width spanning', () => {
      const span: GridSpan = { default: 'col-span-full' }
      const wrapper = mount(BentoGridItem, {
        props: { span },
      })
      expect(wrapper.find('div').classes()).toContain('col-span-full')
    })
  })

  describe('responsive span variants', () => {
    it('applies md breakpoint span class', () => {
      const span: GridSpan = { default: 'col-span-1', md: 'col-span-2' }
      const wrapper = mount(BentoGridItem, {
        props: { span },
      })
      const classes = wrapper.find('div').classes()
      expect(classes).toContain('col-span-1')
      expect(classes).toContain('md:col-span-2')
    })

    it('applies lg breakpoint span class', () => {
      const span: GridSpan = { default: 'col-span-1', lg: 'col-span-3' }
      const wrapper = mount(BentoGridItem, {
        props: { span },
      })
      const classes = wrapper.find('div').classes()
      expect(classes).toContain('col-span-1')
      expect(classes).toContain('lg:col-span-3')
    })

    it('applies both md and lg breakpoint span classes', () => {
      const span: GridSpan = { default: 'col-span-1', md: 'col-span-2', lg: 'col-span-3' }
      const wrapper = mount(BentoGridItem, {
        props: { span },
      })
      const classes = wrapper.find('div').classes()
      expect(classes).toContain('col-span-1')
      expect(classes).toContain('md:col-span-2')
      expect(classes).toContain('lg:col-span-3')
    })

    it('does not apply md class when md is undefined', () => {
      const span: GridSpan = { default: 'col-span-1', lg: 'col-span-3' }
      const wrapper = mount(BentoGridItem, {
        props: { span },
      })
      const classes = wrapper.find('div').classes()
      expect(classes).not.toContain('md:col-span-undefined')
      expect(classes).not.toContain('md:col-span-1')
    })

    it('does not apply lg class when lg is undefined', () => {
      const span: GridSpan = { default: 'col-span-1', md: 'col-span-2' }
      const wrapper = mount(BentoGridItem, {
        props: { span },
      })
      const classes = wrapper.find('div').classes()
      expect(classes).not.toContain('lg:col-span-undefined')
    })

    it('applies md:col-span-full for responsive full-width', () => {
      const span: GridSpan = { default: 'col-span-1', md: 'col-span-full' }
      const wrapper = mount(BentoGridItem, {
        props: { span },
      })
      const classes = wrapper.find('div').classes()
      expect(classes).toContain('col-span-1')
      expect(classes).toContain('md:col-span-full')
    })

    it('applies lg:col-span-full for responsive full-width', () => {
      const span: GridSpan = { default: 'col-span-1', lg: 'col-span-full' }
      const wrapper = mount(BentoGridItem, {
        props: { span },
      })
      const classes = wrapper.find('div').classes()
      expect(classes).toContain('col-span-1')
      expect(classes).toContain('lg:col-span-full')
    })
  })

  describe('full-width spanning', () => {
    it('applies col-span-full for full-width card', () => {
      const span: GridSpan = { default: 'col-span-full' }
      const wrapper = mount(BentoGridItem, {
        props: { span },
      })
      expect(wrapper.find('div').classes()).toContain('col-span-full')
    })

    it('full-width card can have responsive variants', () => {
      const span: GridSpan = { default: 'col-span-2', md: 'col-span-full' }
      const wrapper = mount(BentoGridItem, {
        props: { span },
      })
      const classes = wrapper.find('div').classes()
      expect(classes).toContain('col-span-2')
      expect(classes).toContain('md:col-span-full')
    })
  })

  describe('typical dashboard layout patterns', () => {
    it('supports small card pattern (1 column)', () => {
      const span: GridSpan = { default: 'col-span-1' }
      const wrapper = mount(BentoGridItem, {
        props: { span },
      })
      expect(wrapper.find('div').classes()).toContain('col-span-1')
    })

    it('supports medium card pattern (2 columns on desktop)', () => {
      const span: GridSpan = { default: 'col-span-1', md: 'col-span-2' }
      const wrapper = mount(BentoGridItem, {
        props: { span },
      })
      const classes = wrapper.find('div').classes()
      expect(classes).toContain('col-span-1')
      expect(classes).toContain('md:col-span-2')
    })

    it('supports large card pattern (3 columns on large screens)', () => {
      const span: GridSpan = { default: 'col-span-1', md: 'col-span-2', lg: 'col-span-3' }
      const wrapper = mount(BentoGridItem, {
        props: { span },
      })
      const classes = wrapper.find('div').classes()
      expect(classes).toContain('col-span-1')
      expect(classes).toContain('md:col-span-2')
      expect(classes).toContain('lg:col-span-3')
    })

    it('supports team performance card pattern (full width)', () => {
      const span: GridSpan = { default: 'col-span-full' }
      const wrapper = mount(BentoGridItem, {
        props: { span },
      })
      expect(wrapper.find('div').classes()).toContain('col-span-full')
    })

    it('supports deadline alert card pattern (2 columns on lg)', () => {
      const span: GridSpan = { default: 'col-span-1', md: 'col-span-1', lg: 'col-span-2' }
      const wrapper = mount(BentoGridItem, {
        props: { span },
      })
      const classes = wrapper.find('div').classes()
      expect(classes).toContain('col-span-1')
      expect(classes).toContain('md:col-span-1')
      expect(classes).toContain('lg:col-span-2')
    })
  })
})
