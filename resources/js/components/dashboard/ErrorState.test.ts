/**
 * ErrorState Component Unit Tests
 *
 * Tests the ErrorState component for proper rendering of error messages,
 * conditional retry button display, and callback invocation.
 *
 * @see Requirements: 17.1-17.6
 */

import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import ErrorState from './ErrorState.vue'

describe('ErrorState', () => {
  describe('renders error message', () => {
    it('displays the provided error message', () => {
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Gagal memuat data task overdue',
        },
      })
      expect(wrapper.text()).toContain('Gagal memuat data task overdue')
    })

    it('displays different error messages correctly', () => {
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Gagal memuat data performa tim',
        },
      })
      expect(wrapper.text()).toContain('Gagal memuat data performa tim')
    })

    it('renders message in a paragraph element', () => {
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Error message',
        },
      })
      const paragraph = wrapper.find('p.text-sm.text-muted-foreground')
      expect(paragraph.exists()).toBe(true)
      expect(paragraph.text()).toContain('Error message')
    })

    it('applies text muted and small text classes to message', () => {
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Error message',
        },
      })
      const paragraph = wrapper.find('p')
      expect(paragraph.classes()).toContain('text-sm')
      expect(paragraph.classes()).toContain('text-muted-foreground')
    })

    it('displays error icon with Trustmedis red color', () => {
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Error message',
        },
      })
      // Error icon container should have red border and pale background
      const iconContainer = wrapper.find('div.rounded-xl.border-tm-danger')
      expect(iconContainer.exists()).toBe(true)
      expect(iconContainer.classes()).toContain('bg-tm-danger-pale')
    })

    it('applies neo-brutalism styling to icon container', () => {
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Error message',
        },
      })
      const iconContainer = wrapper.find('div.rounded-xl')
      expect(iconContainer.classes()).toContain('border-[1.5px]')
      // Should have shadow styling
      const hasShadow = iconContainer.classes().some(c => c.includes('shadow-['))
      expect(hasShadow).toBe(true)
    })

    it('has role="alert" attribute for accessibility', () => {
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Error message',
        },
      })
      expect(wrapper.find('[role="alert"]').exists()).toBe(true)
    })

    it('has aria-live="polite" for screen reader announcements', () => {
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Error message',
        },
      })
      const container = wrapper.find('[aria-live="polite"]')
      expect(container.exists()).toBe(true)
    })

    it('maintains minimum height to prevent layout shift', () => {
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Error message',
        },
      })
      const container = wrapper.find('div.flex.flex-col')
      expect(container.classes()).toContain('min-h-[200px]')
    })
  })

  describe('retry button renders when onRetry provided', () => {
    it('displays retry button when onRetry prop is provided', () => {
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Error message',
          onRetry: () => {},
        },
      })
      expect(wrapper.find('button').exists()).toBe(true)
    })

    it('displays "Coba Lagi" text on the retry button', () => {
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Error message',
          onRetry: () => {},
        },
      })
      expect(wrapper.find('button').text()).toContain('Coba Lagi')
    })

    it('renders RefreshCw icon in the retry button', () => {
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Error message',
          onRetry: () => {},
        },
      })
      const button = wrapper.find('button')
      const icon = button.find('svg')
      expect(icon.exists()).toBe(true)
    })

    it('applies neo-brutalism styling to retry button', () => {
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Error message',
          onRetry: () => {},
        },
      })
      const button = wrapper.find('button')
      expect(button.classes()).toContain('border-[1.5px]')
      expect(button.classes()).toContain('border-tm-navy')
      expect(button.classes()).toContain('bg-tm-navy-pale')
    })

    it('applies focus styles to retry button for accessibility', () => {
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Error message',
          onRetry: () => {},
        },
      })
      const button = wrapper.find('button')
      expect(button.classes()).toContain('focus:outline-none')
      expect(button.classes()).toContain('focus:ring-2')
    })

    it('has navy text color on retry button', () => {
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Error message',
          onRetry: () => {},
        },
      })
      const button = wrapper.find('button')
      expect(button.classes()).toContain('text-tm-navy')
    })

    it('applies hover transition classes to retry button', () => {
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Error message',
          onRetry: () => {},
        },
      })
      const button = wrapper.find('button')
      expect(button.classes()).toContain('transition-colors')
      expect(button.classes()).toContain('duration-200')
    })
  })

  describe('retry button is hidden when onRetry not provided', () => {
    it('does not display retry button when onRetry is not provided', () => {
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Error message',
        },
      })
      expect(wrapper.find('button').exists()).toBe(false)
    })

    it('does not display retry button when onRetry is undefined', () => {
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Error message',
          onRetry: undefined,
        },
      })
      expect(wrapper.find('button').exists()).toBe(false)
    })

    it('still displays error message when no retry button', () => {
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Critical error occurred',
        },
      })
      expect(wrapper.text()).toContain('Critical error occurred')
      expect(wrapper.find('button').exists()).toBe(false)
    })

    it('still displays error icon when no retry button', () => {
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Error message',
        },
      })
      expect(wrapper.find('div.rounded-xl.border-tm-danger').exists()).toBe(true)
    })
  })

  describe('clicking retry button calls onRetry callback', () => {
    it('calls onRetry callback when retry button is clicked', async () => {
      const onRetry = vi.fn()
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Error message',
          onRetry,
        },
      })

      await wrapper.find('button').trigger('click')
      expect(onRetry).toHaveBeenCalledTimes(1)
    })

    it('calls onRetry callback multiple times on multiple clicks', async () => {
      const onRetry = vi.fn()
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Error message',
          onRetry,
        },
      })

      const button = wrapper.find('button')
      await button.trigger('click')
      await button.trigger('click')
      await button.trigger('click')

      expect(onRetry).toHaveBeenCalledTimes(3)
    })

    it('calls the correct onRetry function when callback changes', async () => {
      const onRetry1 = vi.fn()
      const onRetry2 = vi.fn()

      const wrapper = mount(ErrorState, {
        props: {
          message: 'Error message',
          onRetry: onRetry1,
        },
      })

      await wrapper.find('button').trigger('click')
      expect(onRetry1).toHaveBeenCalledTimes(1)

      await wrapper.setProps({ onRetry: onRetry2 })
      await wrapper.find('button').trigger('click')
      expect(onRetry2).toHaveBeenCalledTimes(1)
      expect(onRetry1).toHaveBeenCalledTimes(1) // Not called again
    })

    it('handles async onRetry callbacks', async () => {
      const onRetry = vi.fn().mockResolvedValue(undefined)
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Error message',
          onRetry,
        },
      })

      await wrapper.find('button').trigger('click')
      expect(onRetry).toHaveBeenCalledTimes(1)
    })

    it('works with arrow function callbacks', async () => {
      let retryCount = 0
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Error message',
          onRetry: () => {
            retryCount++
          },
        },
      })

      await wrapper.find('button').trigger('click')
      expect(retryCount).toBe(1)
    })
  })

  describe('typical dashboard use cases', () => {
    it('renders error state for overdue tasks data failure', () => {
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Gagal memuat data task overdue',
          onRetry: () => {},
        },
      })
      expect(wrapper.text()).toContain('Gagal memuat data task overdue')
      expect(wrapper.find('button').text()).toContain('Coba Lagi')
    })

    it('renders error state for chart data failure without retry', () => {
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Gagal memuat data chart',
        },
      })
      expect(wrapper.text()).toContain('Gagal memuat data chart')
      expect(wrapper.find('button').exists()).toBe(false)
    })

    it('renders error state for team performance data failure', () => {
      const onRetry = vi.fn()
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Gagal memuat data performa tim',
          onRetry,
        },
      })
      expect(wrapper.text()).toContain('Gagal memuat data performa tim')
      expect(wrapper.find('button').exists()).toBe(true)
    })

    it('renders error state for task list data failure', () => {
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Gagal memuat daftar task',
          onRetry: () => {},
        },
      })
      expect(wrapper.text()).toContain('Gagal memuat daftar task')
    })
  })

  describe('accessibility', () => {
    it('has proper ARIA attributes', () => {
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Error message',
        },
      })
      const container = wrapper.find('[role="alert"]')
      expect(container.exists()).toBe(true)
      expect(container.attributes('aria-live')).toBe('polite')
    })

    it('button is focusable via keyboard', () => {
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Error message',
          onRetry: () => {},
        },
      })
      const button = wrapper.find('button')
      expect(button.element.tagName).toBe('BUTTON')
    })

    it('button has visible focus indicator', () => {
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Error message',
          onRetry: () => {},
        },
      })
      const button = wrapper.find('button')
      expect(button.classes()).toContain('focus:ring-2')
      expect(button.classes()).toContain('focus:ring-tm-navy/50')
      expect(button.classes()).toContain('focus:ring-offset-2')
    })
  })

  describe('layout stability', () => {
    it('maintains consistent padding', () => {
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Error message',
        },
      })
      const container = wrapper.find('div.flex.flex-col')
      expect(container.classes()).toContain('py-8')
    })

    it('centers content horizontally and vertically', () => {
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Error message',
        },
      })
      const container = wrapper.find('div.flex.flex-col')
      expect(container.classes()).toContain('items-center')
      expect(container.classes()).toContain('justify-center')
    })

    it('has text center alignment', () => {
      const wrapper = mount(ErrorState, {
        props: {
          message: 'Error message',
        },
      })
      const container = wrapper.find('div.flex.flex-col')
      expect(container.classes()).toContain('text-center')
    })
  })
})
