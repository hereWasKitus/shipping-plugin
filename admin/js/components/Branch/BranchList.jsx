import React from 'react';
import Branch from './Branch';
import { useSelector, useDispatch } from 'react-redux';
import { addBranch, selectBranches } from '../../features/branchesSlice';

function BranchList() {
  const branches = useSelector(selectBranches);
  const dispatch = useDispatch();

  const handleClick = (e) => {
    e.preventDefault();
    dispatch(addBranch());
  }

  return (
    <>
      <ul className='delivery-branch-list'>
        {branches.length && branches.map(b => (
          <li key={b.id}>
            <Branch
              id={b.id}
              holidays={b.holidays}
              name={b.name}
              schedule={b.schedule}
              isDefault={b.isDefault}
            />
          </li>
        ))}
        <li><button className='button button-primary' onClick={handleClick}>Add branch</button></li>
      </ul>
    </>
  );
}

export default BranchList;
