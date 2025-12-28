/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    
    theme: {
        extend: {},
    },

    plugins: [
        require('@tailwindcss/forms'),
        require('daisyui'),
    ],

    daisyui: {
        themes: [
            {
                expenseTheme: {
                    "color-scheme": "light",
                    "primary": "#3B82F6",
                    "secondary": "#10B981",
                    "accent": "#8B5CF6",
                    "neutral": "#1F2937",
                    "neutral-content": "#D1D5DB",
                    "base-100": "#FFFFFF",
                    "base-200": "#F3F4F6",
                    "base-300": "#D1D5DB",
                    "base-content": "#1F2937",
                    "info": "#3ABFF8",
                    "success": "#36D399",
                    "warning": "#FBBD23",
                    "error": "#F87272",
                },
            },
            "light",
            "dark",
        ],
        darkTheme: "dark",
    },
}