import '@mdi/font/css/materialdesignicons.css';
import 'vuetify/styles';
import { createVuetify } from 'vuetify';

export default createVuetify({
    ssr: typeof import.meta.env !== 'undefined' && !!import.meta.env.SSR,
    // Minimal config for now; theme (dark, #121212, etc.) is a separate task in the plan.
});
