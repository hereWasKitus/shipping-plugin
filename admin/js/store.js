import { configureStore } from '@reduxjs/toolkit';
import branchesReducer from './features/branchesSlice';

export const store = configureStore({
  reducer: {
    branches: branchesReducer,
  },
});
