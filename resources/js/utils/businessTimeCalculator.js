const WORK_START_HOUR = 8;
const WORK_END_HOUR = 18;
const TIMEZONE = 'America/Mexico_City';

export function isCurrentlyInBusinessHours() {
    const now = new Date();
    const mexicoTime = new Date(now.toLocaleString('en-US', { timeZone: TIMEZONE }));
    
    const dayOfWeek = mexicoTime.getDay();
    if (dayOfWeek === 0 || dayOfWeek === 6) {
        return false;
    }
    
    const hour = mexicoTime.getHours();
    return hour >= WORK_START_HOUR && hour < WORK_END_HOUR;
}

export function calculateBusinessHours(startDate, endDate) {
    const start = new Date(startDate);
    const end = new Date(endDate);
    
    if (start >= end) {
        return {
            days: 0,
            hours: 0,
            minutes: 0,
            seconds: 0,
            totalSeconds: 0,
            formatted: '0s'
        };
    }
    
    let totalSeconds = 0;
    let current = new Date(start);
    
    while (current < end) {
        if (isBusinessDay(current)) {
            const dayStart = new Date(current);
            dayStart.setHours(WORK_START_HOUR, 0, 0, 0);
            
            const dayEnd = new Date(current);
            dayEnd.setHours(WORK_END_HOUR, 0, 0, 0);
            
            let periodStart = new Date(current);
            let periodEnd = isSameDay(current, end) ? new Date(end) : new Date(dayEnd);
            
            if (periodStart < dayStart) {
                periodStart = new Date(dayStart);
            }
            
            if (periodStart >= dayEnd) {
                current.setDate(current.getDate() + 1);
                current.setHours(0, 0, 0, 0);
                continue;
            }
            
            if (periodEnd > dayEnd) {
                periodEnd = new Date(dayEnd);
            }
            
            if (periodStart < periodEnd) {
                const secondsInPeriod = Math.floor((periodEnd - periodStart) / 1000);
                totalSeconds += secondsInPeriod;
            }
        }
        
        current.setDate(current.getDate() + 1);
        current.setHours(0, 0, 0, 0);
    }
    
    return formatDuration(totalSeconds);
}

function isBusinessDay(date) {
    const dayOfWeek = date.getDay();
    return dayOfWeek !== 0 && dayOfWeek !== 6;
}

function isSameDay(date1, date2) {
    return date1.getFullYear() === date2.getFullYear() &&
           date1.getMonth() === date2.getMonth() &&
           date1.getDate() === date2.getDate();
}

function formatDuration(totalSeconds) {
    const days = Math.floor(totalSeconds / 86400);
    const hours = Math.floor((totalSeconds % 86400) / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;
    
    const parts = [];
    
    if (days > 0) {
        parts.push(`${days}d`);
    }
    if (hours > 0) {
        parts.push(`${hours}h`);
    }
    if (minutes > 0) {
        parts.push(`${minutes}m`);
    }
    if (days === 0 && hours === 0 && minutes === 0) {
        parts.push(`${seconds}s`);
    }
    
    const formatted = parts.length > 0 ? parts.join(' ') : '0s';
    
    return {
        days,
        hours,
        minutes,
        seconds,
        totalSeconds,
        formatted
    };
}

export function getNextBusinessTime() {
    const now = new Date();
    const next = new Date(now);
    
    for (let i = 0; i < 30; i++) {
        const dayOfWeek = next.getDay();
        
        if (dayOfWeek === 0 || dayOfWeek === 6) {
            next.setDate(next.getDate() + 1);
            next.setHours(WORK_START_HOUR, 0, 0, 0);
            continue;
        }
        
        const hour = next.getHours();
        
        if (hour < WORK_START_HOUR) {
            next.setHours(WORK_START_HOUR, 0, 0, 0);
            return next;
        }
        
        if (hour >= WORK_END_HOUR) {
            next.setDate(next.getDate() + 1);
            next.setHours(WORK_START_HOUR, 0, 0, 0);
            continue;
        }
        
        return next;
    }
    
    return null;
}
