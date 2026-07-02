import { donationTypeLabel } from './labels.js';

function formatItemsList(items) {
    return (items ?? [])
        .map((item) => `- ${item.name}: ${item.quantity}${item.unit ? ` ${item.unit}` : ''}`)
        .join('\n');
}

/**
 * Mensaje dirigido al delivery: qué debe llevar, a dónde, y quién lo recibe.
 * No incluye cédula ni el código médico completo, solo nombre y (si aplica)
 * servicio de quien recibe.
 */
export function buildDeliveryMessage(donation) {
    const lines = [
        'Nueva entrega de donación',
        '',
        `Tipo: ${donationTypeLabel(donation.donation_type)}`,
        `Destino: ${donation.location}`,
        '',
        'Artículos:',
        formatItemsList(donation.items),
        '',
        `Quien recibe: ${donation.receiving_doctor_name || '—'}`,
    ];

    if (donation.donation_type === 'insumos_medicos' && donation.receiving_service) {
        lines.push(`Servicio: ${donation.receiving_service}`);
    }

    lines.push(`Contacto: ${donation.contact_number || '—'}`);

    return lines.join('\n');
}

/**
 * Mensaje dirigido a quien recibe: qué le van a entregar y quién lo trae.
 * No incluye cédula.
 */
export function buildReceiverMessage(donation) {
    const lines = [
        'Donación en camino',
        '',
        `Tipo: ${donationTypeLabel(donation.donation_type)}`,
        '',
        'Artículos:',
        formatItemsList(donation.items),
        '',
        `Delivery: ${donation.delivery_name || '—'}`,
        `Contacto del delivery: ${donation.delivery_contact || '—'}`,
        `Vehículo: ${donation.vehicle_type || '—'}`,
    ];

    return lines.join('\n');
}

const VENEZUELA_COUNTRY_CODE = '58';

/**
 * Limpia el teléfono y, si viene en formato local venezolano (empieza con
 * 0, ej. 0424-123-4567), lo normaliza a formato internacional reemplazando
 * el 0 inicial por el código de país 58 (0424... -> 58424...), que es lo
 * que wa.me necesita para abrir el chat correctamente.
 */
function cleanPhoneNumber(phone) {
    const digits = String(phone ?? '').replace(/\D/g, '');

    if (digits.startsWith('0')) {
        return `${VENEZUELA_COUNTRY_CODE}${digits.slice(1)}`;
    }

    return digits;
}

/**
 * @returns {string|null} URL de wa.me, o null si el teléfono queda vacío
 * tras limpiar los caracteres que no son dígitos.
 */
export function buildWhatsAppUrl(phone, message) {
    const cleaned = cleanPhoneNumber(phone);

    if (! cleaned) {
        return null;
    }

    return `https://wa.me/${cleaned}?text=${encodeURIComponent(message)}`;
}

export function getDeliveryWhatsAppUrl(donation) {
    return buildWhatsAppUrl(donation.delivery_contact, buildDeliveryMessage(donation));
}

export function getReceiverWhatsAppUrl(donation) {
    return buildWhatsAppUrl(donation.contact_number, buildReceiverMessage(donation));
}

/**
 * A diferencia de buildWhatsAppUrl(phone, message), wa.me sin número no abre
 * un chat fijo: abre el selector de contacto/grupo de WhatsApp para que
 * quien comparte elija a quién enviárselo.
 */
export function buildWhatsAppShareUrl(message) {
    return `https://wa.me/?text=${encodeURIComponent(message)}`;
}

function formatNeedsList(items) {
    return items
        .map((item) => `- ${item.name}: ${item.quantity_needed}${item.unit ? ` ${item.unit}` : ''}`)
        .join('\n');
}

/**
 * Mensaje para compartir el resumen de necesidades actuales, agrupadas por
 * tipo de donación tal como se ven en la página pública /necesidades, más
 * el link a esa página al final.
 *
 * @param {{type: string, items: object[]}[]} groupedStockNeeds - ver
 * groupStockNeedsByType() en utils/needs.js
 */
export function buildNeedsMessage(groupedStockNeeds, additionalNeeds, publicUrl) {
    const lines = ['Necesidades actuales de la fundación'];

    groupedStockNeeds.forEach((group) => {
        lines.push('', `${donationTypeLabel(group.type)}:`, formatNeedsList(group.items));
    });

    if (additionalNeeds.length > 0) {
        lines.push(
            '',
            'Otras necesidades:',
            additionalNeeds
                .map((need) => `- ${need.description}${need.quantity_needed ? `: ${need.quantity_needed}` : ''}`)
                .join('\n'),
        );
    }

    lines.push('', `Más detalles: ${publicUrl}`);

    return lines.join('\n');
}
