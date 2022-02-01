import React, { useState } from 'react';
import Holidays from './Holidays';
import Schedule from './Schedule';
import { setBranchName, removeBranch, setDefault, setDisabled, setSku, cloneConfigFromDefault } from '../../features/branchesSlice';
import { useDispatch } from 'react-redux';

function Branch({ id, schedule, holidays, name, isDefault, isDisabled, sku }) {
  const [brName, setBrName] = useState(name);
  const [skuVal, setSkuVal] = useState(sku);
  const dispatch = useDispatch();
  const branchClassname = ['delivery-branch', isDisabled ? 'is-disabled' : ''].join(' ');
  const buttonClassname = ['button', isDisabled ? 'button-secondary' : 'button-primary'].join(' ');

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

  const handleBranchDisable = e => {
    e.preventDefault();
    dispatch(setDisabled({
      branchId: id,
      value: !isDisabled
    }))
  }

  const handleSkuBlur = () => {
    dispatch(setSku({
      branchId: id,
      value: skuVal
    }));
  }

  const cloneConfiguration = e => {
    e.preventDefault();
    dispatch(cloneConfigFromDefault(id));
  }

  return (
    <details className={branchClassname}>
      <summary>
        <input type="text" value={brName} onBlur={handleBlur} onChange={e => setBrName(e.currentTarget.value)} />

        <a href="#" className='delivery-branch__remove-btn' onClick={handleRemoveBranch}>
          <i className="gg-trash"></i>
        </a>

        <span className='delivery-branch__checkbox-container'>
          <input onChange={handleRadioChange} type="radio" name='default-branch' value={id} checked={isDefault} />
        </span>

        <button className={buttonClassname} onClick={handleBranchDisable}>{isDisabled ? 'Enable branch' : 'Disable branch'}</button>
        {!isDefault && <button className="button button-primary" onClick={cloneConfiguration}>Take configuration from default branch</button>}
      </summary>

      <h4>SKU</h4>
      <input type="text" name='sku' value={skuVal} placeholder='sku' onChange={e => setSkuVal(e.target.value)} onBlur={handleSkuBlur} />

      <h4>Schedule</h4>
      <Schedule schedule={schedule} branchId={id} />

      <h4>Holidays</h4>
      <Holidays holidays={holidays} branchId={id} />
    </details>
  );
}

export default Branch;
