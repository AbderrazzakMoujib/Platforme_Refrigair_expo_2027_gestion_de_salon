<x-admin.layout
    :admin="$admin"
    :admin-initials="$adminInitials"
>

    <x-equipment.form
        :equipment-item="$equipmentItem"
        :categories="$categories"
        :page-title="$pageTitle"
        :form-action="$formAction"
        :form-method="$formMethod"
        route-prefix="admin.equipements"
        dashboard-route="admin.index"
        space-label="Administration"
    />

</x-admin.layout>