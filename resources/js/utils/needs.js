// Orden fijo en vez de agrupar por el orden en que llegan los datos, para
// que la lista no "salte" de posición según qué insumos estén bajos hoy.
const DONATION_TYPE_ORDER = ['insumos_medicos', 'higiene', 'alimentos', 'otros'];

export function groupStockNeedsByType(stockNeeds) {
    return DONATION_TYPE_ORDER.map((type) => ({
        type,
        items: stockNeeds.filter((item) => item.donation_type === type),
    })).filter((group) => group.items.length > 0);
}
