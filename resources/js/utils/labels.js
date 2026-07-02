export const DONATION_TYPE_LABELS = {
    insumos_medicos: 'Insumos médicos',
    higiene: 'Higiene',
    alimentos: 'Alimentos',
    otros: 'Otros',
};

export const DONATION_STATUS_LABELS = {
    creada: 'Creada',
    en_proceso: 'En proceso',
    esperando_delivery: 'Esperando delivery',
    en_camino: 'En camino',
    recibido: 'Recibido',
};

export function donationTypeLabel(donationType) {
    return DONATION_TYPE_LABELS[donationType] ?? donationType;
}

export function donationStatusLabel(status) {
    return DONATION_STATUS_LABELS[status] ?? status;
}

export const USER_ROLE_LABELS = {
    admin: 'Admin',
    medico: 'Médico',
    odontologo: 'Odontólogo',
};

export function userRoleLabel(rol) {
    return USER_ROLE_LABELS[rol] ?? rol;
}
