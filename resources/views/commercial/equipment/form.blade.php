<x-commercial.layout
    :commercial="$commercial"
    :commercial-initials="$commercialInitials"
>

    <x-equipment.form
        :equipment-item="$equipmentItem"
        :categories="$categories"
        :page-title="$pageTitle"
        :form-action="$formAction"
        :form-method="$formMethod"
        route-prefix="commercial.equipements"
        dashboard-route="commercial.index"
        space-label="Espace commercial"
    />

</x-commercial.layout>