export const formatDate = (dateString: string): string => {
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  }).format(date);
};

// Pour les heures (format 14h30)
export const formatTime = (time: string): string => {
  return time.replace(':', 'h');
};

// Echappe les caracteres HTML pour eviter les injections XSS dans innerHTML
export const escapeHtml = (text: string): string => {
  const map: Record<string, string> = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
  return text.replace(/[&<>"']/g, (char) => map[char]);
};

// Pour le nombre d'heures (2.5 -> 2h30)
export const formatHours = (hours: string | number): string => {
  const numHours = typeof hours === 'string' ? parseFloat(hours) : hours;
  if (isNaN(numHours)) return '0h';

  const fullHours = Math.floor(numHours);
  const minutes = Math.round((numHours - fullHours) * 60);

  if (minutes === 0) {
    return `${fullHours}h`;
  }
  return `${fullHours}h${minutes.toString().padStart(2, '0')}`;
};