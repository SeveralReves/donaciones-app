// Orden fijo en vez de agrupar por el orden en que llegan los datos, para
// que la lista no "salte" de posición según qué insumos estén bajos hoy.
// Debe mantenerse en sincronía con los slugs de donation_types
// (DonationTypeSeeder) — un slug que falte acá hace que sus insumos
// desaparezcan en silencio de esta agrupación (bug real que pasó con
// 'medicinas', ver git blame).
const DONATION_TYPE_ORDER = ['insumos_medicos', 'medicinas', 'higiene', 'alimentos', 'miscelaneos', 'otros'];

export function groupStockNeedsByType(stockNeeds) {
    return DONATION_TYPE_ORDER.map((type) => ({
        type,
        items: stockNeeds.filter((item) => item.donation_type === type),
    })).filter((group) => group.items.length > 0);
}
