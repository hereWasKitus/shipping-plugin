import { createSlice } from "@reduxjs/toolkit";
import { v4 as uuid } from "uuid";

const createEmptyBranch = (name = '') => {
  const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

  const branch = {
    id: uuid(),
    name: name || 'new branch',
    schedule: {},
    holidays: [],
    isDefault: false,
    isDisabled: false
  }

  days.forEach(day => {
    branch.schedule[day] = {
      slots: [],
      nextDayDelivery: '',
      preparationTime: '',
    };
  });

  return branch;
}

const initialState = wpdata.branches.length ? JSON.parse(wpdata.branches) : [createEmptyBranch()];

export const branchesSlice = createSlice({
  name: 'branches',
  initialState,
  reducers: {
    addBranch: (state) => {
      state.push(createEmptyBranch());
    },

    removeBranch: (state, {payload: {branchId}}) => {
      return [...state.filter(b => b.id !== branchId)];
    },

    setDefault: (state, {payload: branchId}) => {
      const prev = state.find(b => b.isDefault === true);

      if ( prev ) {
        prev.isDefault = false;
      }

      const needle = state.find(b => b.id === branchId);
      needle.isDefault = true;
    },

    setBranchName: (state, {payload: {branchId, value}}) => {
      const newBranch = state.find(b => b.id === branchId);
      newBranch.name = value;
    },

    addSlot: (state, { payload: {branchId, day} }) => {
      const newBranch = state.find(b => b.id === branchId);
      newBranch.schedule[day].slots = [...newBranch.schedule[day].slots, ['', '']];
    },

    removeSlot: (state, { payload: {branchId, day, index} }) => {
      const branch = state.find(b => b.id === branchId );
      branch.schedule[day].slots = branch.schedule[day].slots.filter((slot, slotIndex) => slotIndex !== index );
    },

    editSlot: (state, {payload: {branchId, day, index, slot}}) => {
      const branch = state.find(b => b.id === branchId );
      branch.schedule[day].slots[index] = slot;
    },

    editDay: (state, {payload: {branchId, day, data}}) => {
      const branch = state.find(b => b.id === branchId );
      branch.schedule[day] = {
        ...branch.schedule[day],
        ...data
      }
    },

    setHolidays: (state, {payload: {branchId, holidays}}) => {
      const branch = state.find(b => b.id === branchId );
      branch.holidays = holidays;
    },

    setDisabled: (state, {payload: {branchId, value}}) => {
      const branch = state.find(b => b.id === branchId );
      branch.isDisabled = value;
    }
  }
})

export const {
  addBranch,
  setDefault,
  addSlot,
  removeSlot,
  editSlot,
  editDay,
  setHolidays,
  setBranchName,
  removeBranch,
  setDisabled } = branchesSlice.actions;

export const selectBranches = state => state.branches;

export default branchesSlice.reducer;