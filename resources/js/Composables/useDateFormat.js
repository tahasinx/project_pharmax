import { usePage } from '@inertiajs/vue3'

export function useDateFormat() {
    const page = usePage()

    const formatDate = (date, format = null) => {
        if (!date) return ''

        const dateObj = new Date(date)
        const dateFormat = format || page.props.ui?.date_format || 'Y-m-d'

        // Convert PHP date format to JavaScript
        const formatMap = {
            'Y-m-d': 'yyyy-MM-dd',
            'd-m-Y': 'dd-MM-yyyy',
            'm/d/Y': 'MM/dd/yyyy',
            'd/m/Y': 'dd/MM/yyyy',
            'Y/m/d': 'yyyy/MM/dd',
            'M d, Y': 'MMM dd, yyyy',
            'd M Y': 'dd MMM yyyy',
            'F d, Y': 'MMMM dd, yyyy'
        }

        const jsFormat = formatMap[dateFormat] || 'yyyy-MM-dd'

        return dateObj.toLocaleDateString('en-US', {
            year: 'numeric',
            month: jsFormat.includes('MMM') ? 'short' : jsFormat.includes('MMMM') ? 'long' : '2-digit',
            day: '2-digit'
        })
    }

    return {
        formatDate
    }
}
