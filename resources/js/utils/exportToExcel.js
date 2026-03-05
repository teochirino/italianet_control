import * as XLSX from 'xlsx';

export const exportToExcel = (data, filename = 'reporte_actividades.xlsx') => {
    const worksheet = XLSX.utils.json_to_sheet(data);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, 'Actividades');
    
    const colWidths = [
        { wch: 20 },
        { wch: 30 },
        { wch: 25 },
        { wch: 15 },
        { wch: 40 },
        { wch: 30 },
    ];
    worksheet['!cols'] = colWidths;
    
    XLSX.writeFile(workbook, filename);
};

export const prepareActivityDataForExport = (activities) => {
    return activities.map(activity => {
        const baseData = {
            'Fecha y Hora': new Date(activity.datetime).toLocaleString('es-MX'),
            'Usuario': activity.user.name,
            'Email': activity.user.email,
            'Tipo': activity.type === 'login' ? 'Inicio de Sesión' : 'Cambio de Color',
        };

        if (activity.type === 'login') {
            return {
                ...baseData,
                'Detalles': 'Inicio de sesión',
                'IP': activity.ip_address || 'N/A',
                'Navegador': activity.user_agent ? activity.user_agent.substring(0, 50) : 'N/A',
            };
        } else {
            return {
                ...baseData,
                'Detalles': `${activity.station?.name} - ${activity.attribute?.name}`,
                'Cambio': `${activity.previous_color} → ${activity.new_color}`,
                'Comentario': activity.comment || 'Sin comentarios',
            };
        }
    });
};
