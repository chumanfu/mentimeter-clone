import './bootstrap';
import './presentation-form';

import Alpine from 'alpinejs';
import { registerPresentationSummary } from './dashboard-summary';

registerPresentationSummary(Alpine);

window.Alpine = Alpine;

Alpine.start();
