import { usePage } from '@inertiajs/vue3'

export function useMoney() {
    const page = usePage()

    const money = (value) => {
        const amount = Number(value || 0).toFixed(2)
        const symbol = page.props.ui?.currency_symbol || ''
        return page.props.ui?.currency_position === 'after' ? `${amount}${symbol}` : `${symbol}${amount}`
    }

    return { money }
}
