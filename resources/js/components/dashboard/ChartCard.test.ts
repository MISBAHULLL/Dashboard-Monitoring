/**
 * ChartCard Component Unit Tests
 *
 * Tests the ChartCard component for proper rendering with props,
 * chart type variants, loading states, and accessibility.
 *
 * @see Requirements: 5.1, 5.2, 5.5, 12.4
 */

import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { defineComponent, h, ref, nextTick } from 'vue'
import type { ApexOptions } from 'apexcharts'

// Mock vue3-apexcharts before importing ChartCard
vi.mock('vue3-apexcharts', () => ({
  default: defineComponent({
    name: 'VueApexCharts',
    props: ['type', 'height', 'options', 'series'],
    template: '<div class="vue-apexcharts-stub"></div>',
  }),
}))

// Import ChartCard AFTER the mock
import ChartCard from './ChartCard.vue'

describe('ChartCard', () => {
  // Default test props for area chart
  const defaultAreaProps = {
    title: 'Task Completion Trend',
    chartType: 'area' as const,
    options: {
      chart: { id: 'test-chart' },
      xaxis: { categories: ['Jan', 'Feb', 'Mar'] },
    } as ApexOptions,
    series: [{ name: 'Tasks', data: [10, 20, 30] }],
  }

  // Default test props for donut chart
  const defaultDonutProps = {
    title: 'Task Distribution',
    chartType: 'donut' as const,
    options: {
      chart: { id: 'test-donut' },
      labels: ['Open', 'In Progress', 'Completed'],
    } as ApexOptions,
    series: [44, 55, 13],
  }

  describe('renders area chart correctly', () => {
    it('renders an article element', () => {
      const wrapper = mount(ChartCard, { props: defaultAreaProps })
      expect(wrapper.find('article').exists()).toBe(true)
    })

    it('renders the title text', () => {
      const wrapper = mount(ChartCard, { props: defaultAreaProps })
      expect(wrapper.text()).toContain('Task Completion Trend')
    })

    it('renders the subtitle when provided', () => {
      const wrapper = mount(ChartCard, {
        props: { ...defaultAreaProps, subtitle: 'Last 30 days' },
      })
      expect(wrapper.text()).toContain('Last 30 days')
    })

    it('renders VueApexCharts component', () => {
      const wrapper = mount(ChartCard, { props: defaultAreaProps })
      expect(wrapper.find('.vue-apexcharts-stub').exists()).toBe(true)
    })

    it('applies neo-brutalism base styles', () => {
      const wrapper = mount(ChartCard, { props: defaultAreaProps })
      const article = wrapper.find('article')
      expect(article.classes()).toContain('rounded-xl')
      expect(article.classes()).toContain('border-[1.5px]')
      expect(article.classes()).toContain('border-border')
      expect(article.classes()).toContain('bg-card')
      expect(article.classes()).toContain('p-6')
    })

    it('applies shadow styles', () => {
      const wrapper = mount(ChartCard, { props: defaultAreaProps })
      const article = wrapper.find('article')
      const hasShadow = article.classes().some(c => c.includes('shadow-['))
      expect(hasShadow).toBe(true)
    })

    it('renders header with title', () => {
      const wrapper = mount(ChartCard, { props: defaultAreaProps })
      expect(wrapper.find('header').exists()).toBe(true)
      expect(wrapper.find('header').text()).toContain('Task Completion Trend')
    })
  })

  describe('renders donut chart correctly', () => {
    it('renders an article element', () => {
      const wrapper = mount(ChartCard, { props: defaultDonutProps })
      expect(wrapper.find('article').exists()).toBe(true)
    })

    it('renders the title text', () => {
      const wrapper = mount(ChartCard, { props: defaultDonutProps })
      expect(wrapper.text()).toContain('Task Distribution')
    })

    it('renders VueApexCharts component', () => {
      const wrapper = mount(ChartCard, { props: defaultDonutProps })
      expect(wrapper.find('.vue-apexcharts-stub').exists()).toBe(true)
    })

    it('applies flex justify-center for donut chart centering', () => {
      const wrapper = mount(ChartCard, { props: defaultDonutProps })
      const chartContainer = wrapper.find('.flex.justify-center')
      expect(chartContainer.exists()).toBe(true)
    })

    it('applies neo-brutalism base styles', () => {
      const wrapper = mount(ChartCard, { props: defaultDonutProps })
      const article = wrapper.find('article')
      expect(article.classes()).toContain('rounded-xl')
      expect(article.classes()).toContain('border-[1.5px]')
      expect(article.classes()).toContain('bg-card')
      expect(article.classes()).toContain('p-6')
    })
  })

  describe('loading skeleton displays', () => {
    it('displays loading skeleton when loading is true', () => {
      const wrapper = mount(ChartCard, {
        props: { ...defaultAreaProps, loading: true },
      })
      expect(wrapper.find('.animate-pulse').exists()).toBe(true)
    })

    it('hides chart when loading is true', () => {
      const wrapper = mount(ChartCard, {
        props: { ...defaultAreaProps, loading: true },
      })
      expect(wrapper.find('.vue-apexcharts-stub').exists()).toBe(false)
    })

    it('hides title when loading is true', () => {
      const wrapper = mount(ChartCard, {
        props: { ...defaultAreaProps, loading: true },
      })
      expect(wrapper.text()).not.toContain('Task Completion Trend')
    })

    it('shows chart when loading is false (default)', () => {
      const wrapper = mount(ChartCard, { props: defaultAreaProps })
      expect(wrapper.find('.vue-apexcharts-stub').exists()).toBe(true)
    })

    it('shows title when loading is false (default)', () => {
      const wrapper = mount(ChartCard, { props: defaultAreaProps })
      expect(wrapper.text()).toContain('Task Completion Trend')
    })

    it('has skeleton placeholder elements', () => {
      const wrapper = mount(ChartCard, {
        props: { ...defaultAreaProps, loading: true },
      })
      const skeleton = wrapper.find('.animate-pulse')
      const skeletonBars = skeleton.findAll('div[class*="bg-muted"]')
      expect(skeletonBars.length).toBeGreaterThanOrEqual(2)
    })

    it('applies pulse animation to skeleton', () => {
      const wrapper = mount(ChartCard, {
        props: { ...defaultAreaProps, loading: true },
      })
      expect(wrapper.find('.animate-pulse').exists()).toBe(true)
    })

    it('chart skeleton uses height prop', () => {
      const wrapper = mount(ChartCard, {
        props: { ...defaultAreaProps, loading: true, height: 400 },
      })
      const skeleton = wrapper.find('.animate-pulse')
      const chartSkeleton = skeleton.find('div[style*="height"]')
      expect(chartSkeleton.exists()).toBe(true)
      expect(chartSkeleton.attributes('style')).toContain('height: 400px')
    })

    it('subtitle skeleton only shows when subtitle prop provided', () => {
      const wrapperWithSubtitle = mount(ChartCard, {
        props: { ...defaultAreaProps, loading: true, subtitle: 'Last 30 days' },
      })

      const barsWithSubtitle = wrapperWithSubtitle.find('.animate-pulse').findAll('div[class*="bg-muted"]')
      expect(barsWithSubtitle.length).toBe(3)

      const wrapperWithoutSubtitle = mount(ChartCard, {
        props: { ...defaultAreaProps, loading: true },
      })

      const barsWithoutSubtitle = wrapperWithoutSubtitle.find('.animate-pulse').findAll('div[class*="bg-muted"]')
      expect(barsWithoutSubtitle.length).toBe(2)
    })
  })

  describe('aria-label is present', () => {
    it('has aria-label attribute on article element', () => {
      const wrapper = mount(ChartCard, { props: defaultAreaProps })
      expect(wrapper.find('article').attributes('aria-label')).toBeDefined()
    })

    it('uses title as aria-label', () => {
      const wrapper = mount(ChartCard, { props: defaultAreaProps })
      expect(wrapper.find('article').attributes('aria-label')).toBe('Task Completion Trend')
    })

    it('includes subtitle in aria-label when provided', () => {
      const wrapper = mount(ChartCard, {
        props: { ...defaultAreaProps, subtitle: 'Last 30 days' },
      })
      expect(wrapper.find('article').attributes('aria-label')).toBe('Task Completion Trend - Last 30 days')
    })

    it('has role="img" on article element', () => {
      const wrapper = mount(ChartCard, { props: defaultAreaProps })
      expect(wrapper.find('article').attributes('role')).toBe('img')
    })

    it('updates aria-label when title changes', async () => {
      const wrapper = mount(ChartCard, { props: defaultAreaProps })

      expect(wrapper.find('article').attributes('aria-label')).toBe('Task Completion Trend')

      await wrapper.setProps({ title: 'New Title' })

      expect(wrapper.find('article').attributes('aria-label')).toBe('New Title')
    })

    it('updates aria-label when subtitle changes', async () => {
      const wrapper = mount(ChartCard, {
        props: { ...defaultAreaProps, subtitle: 'Last 30 days' },
      })

      expect(wrapper.find('article').attributes('aria-label')).toBe('Task Completion Trend - Last 30 days')

      await wrapper.setProps({ subtitle: 'Last 7 days' })

      expect(wrapper.find('article').attributes('aria-label')).toBe('Task Completion Trend - Last 7 days')
    })

    it('handles special characters in title', () => {
      const wrapper = mount(ChartCard, {
        props: { ...defaultAreaProps, title: 'Tasks (Completed) & Pending' },
      })
      expect(wrapper.find('article').attributes('aria-label')).toBe('Tasks (Completed) & Pending')
    })
  })

  describe('semantic HTML structure', () => {
    it('uses article element for semantic structure', () => {
      const wrapper = mount(ChartCard, { props: defaultAreaProps })
      expect(wrapper.find('article').exists()).toBe(true)
    })

    it('has header element for title and subtitle', () => {
      const wrapper = mount(ChartCard, {
        props: { ...defaultAreaProps, subtitle: 'Last 30 days' },
      })
      expect(wrapper.find('header').exists()).toBe(true)
    })

    it('title is an h2 element', () => {
      const wrapper = mount(ChartCard, { props: defaultAreaProps })
      expect(wrapper.find('h2').exists()).toBe(true)
      expect(wrapper.find('h2').text()).toContain('Task Completion Trend')
    })

    it('subtitle is a paragraph element', () => {
      const wrapper = mount(ChartCard, {
        props: { ...defaultAreaProps, subtitle: 'Last 30 days' },
      })
      const subtitleElement = wrapper.find('header p')
      expect(subtitleElement.exists()).toBe(true)
      expect(subtitleElement.text()).toBe('Last 30 days')
    })
  })

  describe('hover classes are applied', () => {
    it('has transition classes by default', () => {
      const wrapper = mount(ChartCard, { props: defaultAreaProps })
      const article = wrapper.find('article')
      expect(article.classes()).toContain('transition-all')
      expect(article.classes()).toContain('duration-200')
      expect(article.classes()).toContain('ease-out')
    })

    it('has hover shadow class', () => {
      const wrapper = mount(ChartCard, { props: defaultAreaProps })
      const article = wrapper.find('article')
      const hasHoverShadow = article.classes().some(c => c.includes('hover:shadow-['))
      expect(hasHoverShadow).toBe(true)
    })

    it('has hover translate class', () => {
      const wrapper = mount(ChartCard, { props: defaultAreaProps })
      const article = wrapper.find('article')
      expect(article.classes()).toContain('hover:-translate-y-0.5')
    })
  })

  describe('typical dashboard use cases', () => {
    it('renders Task Completion Trend area chart', () => {
      const wrapper = mount(ChartCard, {
        props: {
          title: 'Task Completion Trend',
          subtitle: 'Last 30 days',
          chartType: 'area',
          options: {
            chart: { id: 'task-completion' },
            xaxis: { categories: ['Week 1', 'Week 2', 'Week 3', 'Week 4'] },
          } as ApexOptions,
          series: [{ name: 'Tasks', data: [10, 15, 20, 25] }],
          height: 250,
        },
      })
      expect(wrapper.text()).toContain('Task Completion Trend')
      expect(wrapper.text()).toContain('Last 30 days')
      expect(wrapper.find('.vue-apexcharts-stub').exists()).toBe(true)
    })

    it('renders Task Distribution donut chart', () => {
      const wrapper = mount(ChartCard, {
        props: {
          title: 'Task Distribution',
          chartType: 'donut',
          options: {
            chart: { id: 'task-distribution' },
            labels: ['Open', 'In Progress', 'Completed', 'Overdue'],
          } as ApexOptions,
          series: [44, 55, 13, 12],
        },
      })
      expect(wrapper.text()).toContain('Task Distribution')
      expect(wrapper.find('.vue-apexcharts-stub').exists()).toBe(true)
    })

    it('renders loading state for chart', () => {
      const wrapper = mount(ChartCard, {
        props: { ...defaultAreaProps, loading: true },
      })
      expect(wrapper.find('.animate-pulse').exists()).toBe(true)
      expect(wrapper.find('.vue-apexcharts-stub').exists()).toBe(false)
    })
  })
})
