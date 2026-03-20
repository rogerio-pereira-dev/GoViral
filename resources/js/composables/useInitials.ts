export type UseInitialsReturn = {
    getInitials: (fullName?: string) => string;
};

export function getInitials(fullName?: string): string {
    if (!fullName) {
        return '';
    }

    const trimmedName = fullName.trim();
    const names = trimmedName.split(' ');

    if (names.length === 0) {
        return '';
    }

    if (names.length === 1) {
        return names[0]
            .charAt(0)
            .toUpperCase();
    }

    const firstInitial = names[0].charAt(0);
    const lastName = names[names.length - 1];
    const lastInitial = lastName.charAt(0);

    return `${firstInitial}${lastInitial}`
        .toUpperCase();
}

export function useInitials(): UseInitialsReturn {
    return { getInitials };
}
