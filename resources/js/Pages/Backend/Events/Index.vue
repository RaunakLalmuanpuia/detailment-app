<template>
    <q-page class="container q-py-lg">
        <!-- Header -->
        <div class="row items-center justify-between q-mb-lg">
            <div class="text-h4 text-weight-bold text-primary">Event Management</div>
            <q-btn color="primary" label="Create New Event" icon="add_circle" @click="openCreateDialog()" rounded />
        </div>

        <!-- Event List -->
        <q-card class="shadow-1 rounded-borders q-mb-lg">
            <q-card-section>
                <div class="text-h6 text-weight-medium text-grey-8 q-mb-md">Upcoming & Past Events</div>
                <q-list separator>
                    <q-item
                        v-for="event in props.events"
                        :key="event.id"
                        clickable
                        @click="openViewDialog(event)"
                        class="q-py-md"
                    >
                        <q-item-section avatar>
                            <q-icon name="event" color="primary" size="md" />
                        </q-item-section>
                        <q-item-section>
                            <q-item-label class="text-subtitle1 text-weight-bold text-grey-9">{{ event.title }}</q-item-label>
                            <q-item-label caption class="text-grey-7">
                                <q-icon name="calendar_today" size="xs" class="q-mr-xs" />{{ formatDate(event.date) }}
                                <span class="q-mx-sm">|</span>
                                <q-icon name="location_on" size="xs" class="q-mr-xs" />{{ event.location }}
                            </q-item-label>
                        </q-item-section>



                        <q-item-section side>
                            <div class="q-gutter-xs">
                                <q-btn dense flat icon="edit" color="primary" @click.stop="openEditDialog(event)" round>
                                    <q-tooltip>Edit Event</q-tooltip>
                                </q-btn>
                                <q-btn dense flat icon="delete" color="negative" @click.stop="confirmDelete(event)" round>
                                    <q-tooltip>Delete Event</q-tooltip>
                                </q-btn>
                            </div>
                        </q-item-section>
                    </q-item>
                </q-list>
                <q-card-section v-if="!props.events || props.events.length === 0" class="text-center text-grey-6">
                    No events found. Click "Create New Event" to add one!
                </q-card-section>
            </q-card-section>
        </q-card>

        <!-- CREATE EVENT DIALOG -->
        <q-dialog v-model="createDialog" persistent>
            <q-card class="event-dialog-card">
                <q-card-section class="bg-primary text-white row items-center">
                    <div class="text-h6 text-weight-bold">Create New Event</div>
                    <q-space />
                    <q-btn icon="close" flat round dense v-close-popup color="white" />
                </q-card-section>
                <q-separator />
                <q-card-section class="q-pa-md">
                    <q-form @submit.prevent="submitCreate">
                        <div class="text-subtitle1 text-weight-bold text-primary q-mb-sm">Basic Event Information</div>
                        <div class="row q-col-gutter-md q-mb-md">
                            <div class="col-12 ">
                                <q-input v-model="createForm.title" label="Event Title" outlined dense hide-bottom-space :rules="[val => !!val || 'Title is required']" />
                            </div>
                            <div class="col-12">
                                <q-input v-model="createForm.location" label="Location" outlined dense hide-bottom-space :rules="[val => !!val || 'Location is required']" />
                            </div>
                            <div class="col-12 col-md-4">
                                <q-input v-model="createForm.date" type="date" label="Date" outlined dense hide-bottom-space :rules="[val => !!val || 'Date is required']" />
                            </div>
                            <div class="col-12 col-md-4">
                                <q-input v-model="createForm.start_time" type="time" label="Start Time" outlined dense hide-bottom-space :rules="[val => !!val || 'Start time is required']" />
                            </div>
                            <div class="col-12 col-md-4">
                                <q-input v-model="createForm.end_time" type="time" label="End Time" outlined dense hide-bottom-space :rules="[val => !!val || 'End time is required']" />
                            </div>
                            <div class="col-12 col-md-6">
                                <q-select
                                    v-model="createForm.status"
                                    label="Status"
                                    :options="['upcoming', 'completed', 'cancelled']"
                                    outlined
                                    dense
                                    hide-bottom-space
                                    :rules="[val => !!val || 'Status is required']"
                                />
                            </div>
                        </div>

                        <q-separator class="q-my-md" />

                        <div class="text-subtitle1 text-weight-bold text-primary q-mb-sm">Guests Details</div>
                        <q-card flat bordered class="q-mb-md">
                            <q-card-section>
                                <div v-for="(guest, i) in createForm.guests" :key="i" class="row q-col-gutter-sm q-mb-sm">
                                    <div class="col-12 col-sm-6 col-md-4">
                                        <q-input v-model="guest.role" label="Role" outlined dense />
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-4">
                                        <q-input v-model="guest.name" label="Name" outlined dense />
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-4">
                                        <q-input v-model="guest.designation" label="Designation" outlined dense />
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-4">
                                        <q-input v-model="guest.organization" label="Organization" outlined dense />
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-7">
                                        <q-input v-model="guest.contact" label="Contact" outlined dense type="email" />
                                    </div>
                                    <div class="col-12 col-sm-auto flex items-center justify-center">
                                        <q-btn dense flat icon="delete" color="negative" @click="removeCreateGuest(i)" round>
                                            <q-tooltip>Remove Guest</q-tooltip>
                                        </q-btn>
                                    </div>
                                </div>
                                <q-btn outline color="primary" icon="person_add" label="Add Guest" @click="addCreateGuest" class="q-mt-sm" />
                            </q-card-section>
                        </q-card>

                        <q-separator class="q-my-md" />

                        <div class="text-subtitle1 text-weight-bold text-primary q-mb-sm">Duty Assignments</div>
                        <q-card flat bordered class="q-mb-md">
                            <q-card-section>
                                <div
                                    v-for="(dutyAssign, dIndex) in createForm.assignments"
                                    :key="dutyAssign.duty.id"
                                    class="q-pa-md q-mb-sm bg-grey-1 rounded-borders"
                                >
                                    <div class="text-weight-bold text-primary text-md q-mb-sm">{{ dutyAssign.duty.name }}</div>

                                    <div
                                        v-for="(emp, eIndex) in dutyAssign.employees"
                                        :key="eIndex"
                                        class="row q-col-gutter-sm q-mb-sm items-center"
                                    >
                                        <div class="col-10">
                                            <q-select
                                                v-model="emp.employee_id"
                                                label="Select Employee"
                                                :options="dutyAssign.availableEmployees"
                                                option-value="id"
                                                option-label="name"
                                                outlined
                                                dense
                                                emit-value
                                                map-options
                                                clearable
                                            />
                                        </div>
                                        <div class="col-auto flex items-center">
                                            <q-btn dense flat icon="delete" color="negative" @click="removeCreateEmployee(dIndex, eIndex)" round>
                                                <q-tooltip>Remove Employee</q-tooltip>
                                            </q-btn>
                                        </div>
                                    </div>

                                    <q-btn dense flat icon="add" color="primary" label="Add Employee to this Duty" @click="addCreateEmployee(dIndex)" class="q-mt-sm" />
                                </div>
                                <div v-if="!createForm.assignments || createForm.assignments.length === 0" class="text-grey-6 text-center q-py-md">
                                    No duties defined for this event.
                                </div>
                            </q-card-section>
                        </q-card>

                        <q-card-actions align="right" class="q-mt-lg q-gutter-md">
                            <q-btn flat label="Cancel" color="negative" v-close-popup />
                            <q-btn label="Create Event" color="primary" type="submit" icon="check_circle" rounded />
                        </q-card-actions>
                    </q-form>
                </q-card-section>
            </q-card>
        </q-dialog>

        <!-- EDIT EVENT DIALOG -->
        <q-dialog v-model="editDialog" persistent>
            <q-card class="event-dialog-card">
                <q-card-section class="bg-primary text-white row items-center">
                    <div class="text-h6 text-weight-bold">Edit Event</div>
                    <q-space />
                    <q-btn icon="close" flat round dense v-close-popup color="white" />
                </q-card-section>
                <q-separator />
                <q-card-section class="q-pa-md">
                    <q-form @submit.prevent="submitEdit">
                        <div class="text-subtitle1 text-weight-bold text-primary q-mb-sm">Basic Event Information</div>
                        <div class="row q-col-gutter-md q-mb-md">
                            <div class="col-12 ">
                                <q-input v-model="editForm.title" label="Event Title" outlined dense hide-bottom-space :rules="[val => !!val || 'Title is required']" />
                            </div>
                            <div class="col-12">
                                <q-input v-model="editForm.location" label="Location" outlined dense hide-bottom-space :rules="[val => !!val || 'Location is required']" />
                            </div>
                            <div class="col-12 col-md-4">
                                <q-input v-model="editForm.date" type="date" label="Date" outlined dense hide-bottom-space :rules="[val => !!val || 'Date is required']" />
                            </div>
                            <div class="col-12 col-md-4">
                                <q-input v-model="editForm.start_time" type="time" label="Start Time" outlined dense hide-bottom-space :rules="[val => !!val || 'Start time is required']" />
                            </div>
                            <div class="col-12 col-md-4">
                                <q-input v-model="editForm.end_time" type="time" label="End Time" outlined dense hide-bottom-space :rules="[val => !!val || 'End time is required']" />
                            </div>
                            <div class="col-12 col-md-6">
                                <q-select
                                    v-model="editForm.status"
                                    label="Status"
                                    :options="['upcoming', 'completed', 'cancelled']"
                                    outlined
                                    dense
                                    hide-bottom-space
                                    :rules="[val => !!val || 'Status is required']"
                                />
                            </div>
                        </div>

                        <q-separator class="q-my-md" />

                        <div class="text-subtitle1 text-weight-bold text-primary q-mb-sm">Guests Details</div>
                        <q-card flat bordered class="q-mb-md">
                            <q-card-section>
                                <div v-for="(guest, i) in editForm.guests" :key="i" class="row q-col-gutter-sm q-mb-sm">
                                    <div class="col-12 col-sm-6 col-md-4">
                                        <q-input v-model="guest.role" label="Role" outlined dense />
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-4">
                                        <q-input v-model="guest.name" label="Name" outlined dense />
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-4">
                                        <q-input v-model="guest.designation" label="Designation" outlined dense />
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-4">
                                        <q-input v-model="guest.organization" label="Organization" outlined dense />
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-7">
                                        <q-input v-model="guest.contact" label="Contact" outlined dense type="email" />
                                    </div>
                                    <div class="col-12 col-sm-auto flex items-center justify-center">
                                        <q-btn dense flat icon="delete" color="negative" @click="removeEditGuest(i)" round>
                                            <q-tooltip>Remove Guest</q-tooltip>
                                        </q-btn>
                                    </div>
                                </div>
                                <q-btn outline color="primary" icon="person_add" label="Add Guest" @click="addEditGuest" class="q-mt-sm" />
                            </q-card-section>
                        </q-card>

                        <q-separator class="q-my-md" />

                        <div class="text-subtitle1 text-weight-bold text-primary q-mb-sm">Duty Assignments</div>
                        <q-card flat bordered class="q-mb-md">
                            <q-card-section>
                                <div
                                    v-for="(dutyAssign, dIndex) in editForm.assignments"
                                    :key="dutyAssign.duty.id"
                                    class="q-pa-md q-mb-sm bg-grey-1 rounded-borders"
                                >
                                    <div class="text-weight-bold text-primary text-md q-mb-sm">{{ dutyAssign.duty.name }}</div>

                                    <div
                                        v-for="(emp, eIndex) in dutyAssign.employees"
                                        :key="eIndex"
                                        class="row q-col-gutter-sm q-mb-sm items-center"
                                    >
                                        <div class="col-10">
                                            <q-select
                                                v-model="emp.employee_id"
                                                label="Select Employee"
                                                :options="dutyAssign.availableEmployees"
                                                option-value="id"
                                                option-label="name"
                                                outlined
                                                dense
                                                emit-value
                                                map-options
                                                clearable
                                            />
                                        </div>
                                        <div class="col-auto flex items-center">
                                            <q-btn dense flat icon="delete" color="negative" @click="removeEditEmployee(dIndex, eIndex)" round>
                                                <q-tooltip>Remove Employee</q-tooltip>
                                            </q-btn>
                                        </div>
                                    </div>

                                    <q-btn dense flat icon="add" color="primary" label="Add Employee to this Duty" @click="addEditEmployee(dIndex)" class="q-mt-sm" />
                                </div>
                                <div v-if="!editForm.assignments || editForm.assignments.length === 0" class="text-grey-6 text-center q-py-md">
                                    No duties defined for this event.
                                </div>
                            </q-card-section>
                        </q-card>

                        <q-card-actions align="right" class="q-mt-lg q-gutter-md">
                            <q-btn flat label="Cancel" color="negative" v-close-popup />
                            <q-btn label="Save Changes" color="primary" type="submit" icon="save" rounded />
                        </q-card-actions>
                    </q-form>
                </q-card-section>
            </q-card>
        </q-dialog>

        <!-- VIEW EVENT DETAILS DIALOG -->
        <q-dialog v-model="viewDialog" persistent>
            <q-card class="event-dialog-card">
                <q-card-section class="bg-primary text-white row items-center">
                    <div class="text-h6 text-weight-bold">{{ selectedEvent?.title }}</div>
                    <q-space />
                    <q-btn icon="close" flat round dense v-close-popup color="white" />
                </q-card-section>
                <q-separator />
                <q-card-section class="q-pa-md">
                    <div class="q-mb-md">
                        <div class="row q-col-gutter-sm">
                            <div class="col-12 col-md-6"><q-icon name="calendar_today" color="grey-7" size="sm" class="q-mr-xs" /><b>Date:</b> {{ formatDate(selectedEvent?.date) }}</div>
                            <div class="col-12 col-md-6"><q-icon name="access_time" color="grey-7" size="sm" class="q-mr-xs" /><b>Time:</b> {{ selectedEvent?.start_time }} - {{ selectedEvent?.end_time }}</div>
                            <div class="col-12 col-md-6"><q-icon name="location_on" color="grey-7" size="sm" class="q-mr-xs" /><b>Location:</b> {{ selectedEvent?.location }}</div>
                            <div class="col-12 col-md-6"><q-icon name="info" color="grey-7" size="sm" class="q-mr-xs" /><b>Status:</b>
                                <q-chip :color="getStatusColor(selectedEvent?.status)" text-color="white" class="text-capitalize" size="sm">
                                    {{ selectedEvent?.status }}
                                </q-chip>
                            </div>
                        </div>
                    </div>

                    <!-- GUESTS SECTION - MODIFIED FOR SINGLE/FEW GUESTS -->
                    <div v-if="selectedEvent?.guests?.length">
                        <q-separator class="q-my-md" />
                        <div class="text-subtitle1 text-weight-bold text-primary q-mb-sm">Guests</div>
                        <!-- Using a single card for all guests or a well-structured div -->
                        <q-card flat bordered class="q-mb-md">
                            <q-card-section v-for="(guest, i) in selectedEvent.guests" :key="i" :class="{'bg-grey-1': i % 2 === 1}">
                                <div class="text-weight-bold text-primary q-mb-xs">{{ guest.name }} <span class="text-grey-7">- {{ guest.role }}</span></div>
                                <div class="row q-col-gutter-sm">
                                    <div class="col-12 col-sm-6 text-grey-8"><q-icon name="work" size="xs" class="q-mr-xs" />{{ guest.designation }}</div>
                                    <div class="col-12 col-sm-6 text-grey-8"><q-icon name="apartment" size="xs" class="q-mr-xs" />{{ guest.organization }}</div>
                                    <div class="col-12 col-sm-6 text-grey-8"><q-icon name="email" size="xs" class="q-mr-xs" />{{ guest.contact }}</div>
                                </div>
                                <q-separator v-if="i < selectedEvent.guests.length - 1" class="q-my-md" />
                            </q-card-section>
                        </q-card>
                    </div>

                    <div v-if="selectedEvent?.duties?.length" class="q-mt-md">
                        <q-separator class="q-my-md" />
                        <div class="text-subtitle1 text-weight-bold text-primary q-mb-sm">Duty Assignments</div>
                        <div class="row q-col-gutter-md">
                            <div v-for="(duty, i) in selectedEvent.duties" :key="i" class="col-12 col-md-6">
                                <q-card flat bordered class="q-mb-sm">
                                    <q-card-section class="q-py-sm bg-grey-1">
                                        <div class="text-weight-bold text-primary">{{ duty.name }}</div>
                                    </q-card-section>
                                    <q-card-section class="q-pt-sm">
                                        <ul class="q-pl-md q-my-none">
                                            <li v-for="emp in duty.employees" :key="emp.id" class="text-grey-8">{{ emp.name }}</li>
                                            <li v-if="!duty.employees || duty.employees.length === 0" class="text-grey-6">No employees assigned</li>
                                        </ul>
                                    </q-card-section>
                                </q-card>
                            </div>
                        </div>
                    </div>
                </q-card-section>
                <q-card-actions align="right" class="q-mt-md">
                    <q-btn flat label="Close" color="primary" v-close-popup icon="close" />
                </q-card-actions>
            </q-card>
        </q-dialog>

        <!-- DELETE CONFIRMATION DIALOG (Optional, using Quasar Notify for simpler confirmation) -->
        <!-- Consider using Quasar's QDialog.create() for a more integrated confirmation experience if preferred over a dedicated dialog component -->

    </q-page>
</template>


<script setup>
import BackendLayout from '../../../Layouts/BackendLayout.vue'
import { ref, watch } from 'vue'
import { router,useForm } from '@inertiajs/vue3'
import { useQuasar } from 'quasar'

defineOptions({ layout: BackendLayout })

const props = defineProps(['events', 'duties', 'employees'])
const $q = useQuasar()

// Dialogs
const createDialog = ref(false)
const editDialog = ref(false)
const viewDialog = ref(false)
const selectedEvent = ref(null)

// Base data
const allEmployees = ref(props.employees)
const dutyOptions = ref(props.duties)

// ----------------- CREATE FORM -----------------
const createForm = useForm({
    title: '',
    date: '',
    start_time: '',
    end_time: '',
    location: '',
    status: 'upcoming',
    guests: [],
    assignments: []
})

// Initialize duties for create
const initializeCreateAssignments = () => {
    createForm.assignments = dutyOptions.value.map(duty => {
        const availableEmployees = allEmployees.value.filter(e =>
            (e.designation?.duty_type || '').toLowerCase().includes(duty.name.toLowerCase())
        )
        return { duty, availableEmployees, employees: [{ employee_id: null }] }
    })
}

// Guests & employees
const addCreateGuest = () => createForm.guests.push({ role: '', name: '', designation: '', organization: '', contact: '' })
const removeCreateGuest = (i) => createForm.guests.splice(i, 1)
const addCreateEmployee = (dIndex) => createForm.assignments[dIndex].employees.push({ employee_id: null })
const removeCreateEmployee = (dIndex, eIndex) => createForm.assignments[dIndex].employees.splice(eIndex, 1)

// Open create dialog
const openCreateDialog = () => {
    createForm.reset()
    initializeCreateAssignments()
    createDialog.value = true
}

// Submit create
const submitCreate = () => {
    createForm.post(route('events.store'), {
        onSuccess: () => {
            $q.notify({ type: 'positive', message: 'Event created successfully!' })
            createDialog.value = false
        }
    })
}

// ----------------- EDIT FORM -----------------
const editForm = useForm({
    id: null,
    title: '',
    date: '',
    start_time: '',
    end_time: '',
    location: '',
    status: 'upcoming',
    guests: [],
    assignments: []
})

// Open edit dialog
const openEditDialog = (event) => {
    editForm.id = event.id
    editForm.title = event.title
    editForm.location = event.location
    editForm.date = event.date
    editForm.start_time = event.start_time
    editForm.end_time = event.end_time
    editForm.status = event.status
    editForm.guests = event.guests?.map(g => ({ ...g })) || []

    // Map duties & employees
    editForm.assignments = dutyOptions.value.map(duty => {
        const eventDuty = event.duties?.find(d => d.id === duty.id)
        const availableEmployees = allEmployees.value.filter(e =>
            (e.designation?.duty_type || '').toLowerCase().includes(duty.name.toLowerCase())
        )
        return {
            duty,
            availableEmployees,
            employees: eventDuty?.employees?.length
                ? eventDuty.employees.map(emp => ({ employee_id: emp.id }))
                : [{ employee_id: null }]
        }
    })

    editDialog.value = true
}

// Guests & employees for edit
const addEditGuest = () => editForm.guests.push({ role: '', name: '', designation: '', organization: '', contact: '' })
const removeEditGuest = (i) => editForm.guests.splice(i, 1)
const addEditEmployee = (dIndex) => editForm.assignments[dIndex].employees.push({ employee_id: null })
const removeEditEmployee = (dIndex, eIndex) => editForm.assignments[dIndex].employees.splice(eIndex, 1)

// Submit edit
const submitEdit = () => {
    editForm.put(route('events.update', editForm.id), {
        onSuccess: () => {
            $q.notify({ type: 'positive', message: 'Event updated successfully!' })
            editDialog.value = false
        }
    })
}

// View event
const openViewDialog = (event) => {
    selectedEvent.value = event
    viewDialog.value = true
}

const confirmDelete = (event) => {
    $q.dialog({
        title: 'Confirm Delete',
        message: `Are you sure you want to delete the event "${event.title}"?`,
        cancel: true,
        persistent: true,
    }).onOk(() =>
        router.delete(route('events.destroy', event), {
        onSuccess: () => {
            $q.notify({ type: 'positive', message: 'Event deleted successfully!' })
        },
        onError: (errors) => {
            $q.notify({ type: 'negative', message: 'Failed to delete event.' })
            console.error(errors)
        }
    }))
}



// Helpers
const formatDate = (date) => date ? new Date(date).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }) : ''
const getStatusColor = (status) => ({ upcoming: 'primary', completed: 'positive', cancelled: 'negative' })[status] || 'grey'

// CREATE FORM WATCHER (no event_id needed)
watch(
    () => [createForm.date, createForm.start_time, createForm.end_time],
    async ([date, start, end]) => {
        if (!date || !start || !end) return
        try {
            const { data } = await axios.post(route('events.available-employees'), {
                date,
                start_time: start,
                end_time: end,
            })
            createForm.assignments.forEach(assignment => {
                assignment.availableEmployees = data.filter(emp =>
                    (emp.designation?.duty_type || '').toLowerCase() === assignment.duty.name.toLowerCase()
                )
            })
        } catch (error) {
            console.error('Failed to fetch available employees', error)
        }
    }
)

// EDIT FORM WATCHER (pass event_id)
watch(
    () => [editForm.date, editForm.start_time, editForm.end_time],
    async ([date, start, end]) => {
        if (!date || !start || !end) return
        try {
            const { data } = await axios.post(route('events.available-employees'), {
                date,
                start_time: start,
                end_time: end,
                event_id: editForm.id, // pass current event id
            })
            editForm.assignments.forEach(assignment => {
                assignment.availableEmployees = data.filter(emp =>
                    (emp.designation?.duty_type || '').toLowerCase() === assignment.duty.name.toLowerCase()
                )
            })
        } catch (error) {
            console.error('Failed to fetch available employees', error)
        }
    }
)

</script>
