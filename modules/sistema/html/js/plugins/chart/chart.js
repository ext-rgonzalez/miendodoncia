/**
 * @namespace Chart
 */
var Chart = require('modules/sistema/html/js/plugins/chart/core/core.js')();

require('modules/sistema/html/js/plugins/chart/core/core.helpers')(Chart);
require('modules/sistema/html/js/plugins/chart/core/core.canvasHelpers')(Chart);
require('modules/sistema/html/js/plugins/chart/core/core.element')(Chart);
require('modules/sistema/html/js/plugins/chart/core/core.animation')(Chart);
require('modules/sistema/html/js/plugins/chart/core/core.controller')(Chart);
require('modules/sistema/html/js/plugins/chart/core/core.datasetController')(Chart);
require('modules/sistema/html/js/plugins/chart/core/core.layoutService')(Chart);
require('modules/sistema/html/js/plugins/chart/core/core.scaleService')(Chart);
require('modules/sistema/html/js/plugins/chart/core/core.plugin.js')(Chart);
require('modules/sistema/html/js/plugins/chart/core/core.scale')(Chart);
require('modules/sistema/html/js/plugins/chart/core/core.title')(Chart);
require('modules/sistema/html/js/plugins/chart/core/core.legend')(Chart);
require('modules/sistema/html/js/plugins/chart/core/core.tooltip')(Chart);

require('modules/sistema/html/js/plugins/chart/elements/element.arc')(Chart);
require('modules/sistema/html/js/plugins/chart/elements/element.line')(Chart);
require('modules/sistema/html/js/plugins/chart/elements/element.point')(Chart);
require('modules/sistema/html/js/plugins/chart/elements/element.rectangle')(Chart);

require('modules/sistema/html/js/plugins/chart/scales/scale.linearbase.js')(Chart);
require('modules/sistema/html/js/plugins/chart/scales/scale.category')(Chart);
require('modules/sistema/html/js/plugins/chart/scales/scale.linear')(Chart);
require('modules/sistema/html/js/plugins/chart/scales/scale.logarithmic')(Chart);
require('modules/sistema/html/js/plugins/chart/scales/scale.radialLinear')(Chart);
require('modules/sistema/html/js/plugins/chart/scales/scale.time')(Chart);

// Controllers must be loaded after elements
// See Chart.core.datasetController.dataElementType
require('modules/sistema/html/js/plugins/chart/controllers/controller.bar')(Chart);
require('modules/sistema/html/js/plugins/chart/controllers/controller.bubble')(Chart);
require('modules/sistema/html/js/plugins/chart/controllers/controller.doughnut')(Chart);
require('modules/sistema/html/js/plugins/chart/controllers/controller.line')(Chart);
require('modules/sistema/html/js/plugins/chart/controllers/controller.polarArea')(Chart);
require('modules/sistema/html/js/plugins/chart/controllers/controller.radar')(Chart);

require('modules/sistema/html/js/plugins/chart/charts/Chart.Bar')(Chart);
require('modules/sistema/html/js/plugins/chart/charts/Chart.Bubble')(Chart);
require('modules/sistema/html/js/plugins/chart/charts/Chart.Doughnut')(Chart);
require('modules/sistema/html/js/plugins/chart/charts/Chart.Line')(Chart);
require('modules/sistema/html/js/plugins/chart/charts/Chart.PolarArea')(Chart);
require('modules/sistema/html/js/plugins/chart/charts/Chart.Radar')(Chart);
require('modules/sistema/html/js/plugins/chart/charts/Chart.Scatter')(Chart);

window.Chart = module.exports = Chart;
