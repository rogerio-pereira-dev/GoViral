import '@mdi/font/css/materialdesignicons.css';
import 'vuetify/styles';
import { createVuetify } from 'vuetify';

const goviralDarkTheme = {
    dark: true,
    colors: {
        background: '#121212',
        surface: '#121212',
        primary: '#FE2C55',
        secondary: '#25F4EE',
        'on-background': '#ffffff',
        'on-surface': '#ffffff',
        'on-primary': '#ffffff',
        'on-secondary': '#121212',
    },
    variables: {
        'body-font-family': "'Inter', ui-sans-serif, system-ui, sans-serif",
        'heading-font-family': "'Space Grotesk', ui-sans-serif, system-ui, sans-serif",
    },
};

export default createVuetify({
    ssr: typeof import.meta.env !== 'undefined' && !!import.meta.env.SSR,
    theme: {
        defaultTheme: 'goviralDark',
        themes: {
            goviralDark: goviralDarkTheme,
        },
    },
});
