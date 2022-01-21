import React from 'react';
import BranchList from './BranchList';
import { Provider } from 'react-redux';
import { store } from '../../store';

function BranchApp() {
  return (
    <Provider store={store}>
      <BranchList />
    </Provider>
  );
}

export default BranchApp;
