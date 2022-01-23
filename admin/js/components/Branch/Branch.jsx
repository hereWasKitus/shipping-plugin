import React, { useState } from 'react';
import Holidays from './Holidays';
import Schedule from './Schedule';
import { setBranchName, removeBranch, setDefault } from '../../features/branchesSlice';
import { useDispatch } from 'react-redux';

function Branch({ id, schedule, holidays, name, isDefault }) {
  const [brName, setBrName] = useState(name);
  const dispatch = useDispatch();

  const handleBlur = () => {
    dispatch(setBranchName({
      branchId: id,
      value: brName
    }));
  }

  const handleRemoveBranch = e => {
    e.preventDefault();
    dispatch(removeBranch({
      branchId: id
    }));
  }

  const handleRadioChange = e => {
    dispatch(setDefault(id));
  }

  return (
    <details className='delivery-branch'>
      <summary>
        <input type="text" value={brName} onBlur={handleBlur} onChange={e => setBrName(e.currentTarget.value)} />
        <a href="#" className='delivery-branch__remove-btn' onClick={handleRemoveBranch}>
          <i className="gg-trash"></i>
        </a>
        <span className='delivery-branch__checkbox-container'>
          <input onChange={handleRadioChange} type="radio" name='default-branch' value={id} checked={isDefault} />
        </span>
      </summary>

      <Schedule schedule={schedule} branchId={id} />

      <h4>Holidays</h4>

      <Holidays holidays={holidays} branchId={id} />
    </details>
  );
}

export default Branch;
